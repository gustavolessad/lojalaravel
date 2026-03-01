<?php

namespace App\Livewire\Shop;

use App\Mail\CustomerWelcome;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Services\AsaasService;
use App\Services\PaymentCalculator;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\ShippingCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CheckoutPage extends Component
{
    // ── Etapa atual (0-4) ─────────────────────────────────────────────────
    // 0 = Identificação (login/cadastro), 1 = Endereço, 2 = Frete, 3 = Pagamento, 4 = Revisão
    public int $step = 1;

    // ── Etapa 0: Autenticação ─────────────────────────────────────────────
    public string $authMode = 'login';   // 'login' | 'register'

    // Login
    public string $loginEmail    = '';
    public string $loginPassword = '';

    // Cadastro
    public string $registerType                 = 'pf';   // 'pf' | 'pj'
    public string $registerName                 = '';
    public string $registerCpf                  = '';
    public string $registerBirthDate            = '';
    public string $registerCompanyName          = '';
    public string $registerCnpj                 = '';
    public string $registerResponsibleName      = '';
    public string $registerEmail                = '';
    public string $registerMobile               = '';
    public string $registerPassword             = '';
    public string $registerPasswordConfirmation = '';

    // ── Etapa 1: Endereço ─────────────────────────────────────────────────
    public ?int $selectedAddressId = null;
    public ?int $editingAddressId  = null;   // null = novo endereço | int = editando existente
    public bool $useNewAddress     = false;

    public string $addrLabel       = '';
    public string $addrName        = '';
    public string $addrPhone       = '';
    public string $addrZip         = '';
    public string $addrStreet      = '';
    public string $addrNumber      = '';
    public string $addrComplement  = '';
    public string $addrDistrict    = '';
    public string $addrCity        = '';
    public string $addrState       = '';

    // ── Etapa 2: Frete ────────────────────────────────────────────────────
    public ?int  $selectedShippingIndex = null;
    public array $shippingOptions       = [];
    public bool  $loadingShipping       = false;

    // ── Etapa 3: Pagamento ────────────────────────────────────────────────
    public string $paymentMethod = 'pix';

    public string $cardHolder   = '';
    public string $cardNumber   = '';
    public string $cardExpiry   = '';   // MM/YY
    public string $cardCvv      = '';
    public int    $installments = 1;

    // ── Etapa 4: Notas ────────────────────────────────────────────────────
    public string $notes = '';

    // ── Estado interno ────────────────────────────────────────────────────
    public bool    $processing   = false;
    public ?string $errorMessage = null;

    // ── Mount ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $cart = app(CartService::class)->current()->load('items');

        if ($cart->items->isEmpty()) {
            $this->redirect(route('cart.index'), navigate: true);
            return;
        }

        $customer = Auth::guard('customer')->user();

        if (! $customer) {
            $this->step = 0;
            return;
        }

        $this->prefillAddress($customer);
    }

    // ── Computed ──────────────────────────────────────────────────────────

    #[Computed]
    public function cart(): Cart
    {
        return app(CartService::class)
            ->current()
            ->load(['items.product', 'items.variant.attributeValues.attribute']);
    }

    #[Computed]
    public function customer(): ?Customer
    {
        return Auth::guard('customer')->user();
    }

    #[Computed]
    public function savedAddresses()
    {
        return $this->customer?->addresses()->orderByDesc('is_default')->get() ?? collect();
    }

    /**
     * Chamado via wire:init no step 2. Calcula as opções de frete de forma
     * assíncrona, permitindo que a tela mude instantaneamente.
     */
    public function loadShipping(): void
    {
        $zip = preg_replace('/\D/', '', $this->addrZip);

        if (strlen($zip) === 8) {
            $this->shippingOptions = app(ShippingCalculator::class)
                ->calculate($zip, (float) $this->cart->subtotal, $this->cart->items);
        }

        $this->loadingShipping = false;
    }

    #[Computed]
    public function selectedShipping(): ?array
    {
        if ($this->selectedShippingIndex === null) {
            return null;
        }

        return $this->shippingOptions[$this->selectedShippingIndex] ?? null;
    }

    #[Computed]
    public function total(): float
    {
        $discount = (float) ($this->cart->coupon_discount ?? 0);

        return max(0, (float) $this->cart->subtotal - $discount)
            + (float) ($this->selectedShipping['price'] ?? 0);
    }

    #[Computed]
    public function installmentOptions(): array
    {
        return app(PaymentCalculator::class)->installmentOptions($this->total);
    }

    #[Computed]
    public function pixTotal(): ?float
    {
        return app(PaymentCalculator::class)->pixPrice($this->total);
    }

    #[Computed]
    public function pixSavings(): float
    {
        return app(PaymentCalculator::class)->pixSavings($this->total);
    }

    // ── Etapa 0: Autenticação ─────────────────────────────────────────────

    public function switchAuthMode(string $mode): void
    {
        $this->authMode = $mode;
        $this->errorMessage = null;
        $this->resetValidation();
    }

    public function attemptLogin(): void
    {
        $this->errorMessage = null;

        $this->validate([
            'loginEmail'    => 'required|email',
            'loginPassword' => 'required|string|min:6',
        ], [
            'loginEmail.required'    => 'Informe seu e-mail.',
            'loginEmail.email'       => 'E-mail inválido.',
            'loginPassword.required' => 'Informe sua senha.',
            'loginPassword.min'      => 'A senha deve ter pelo menos 6 caracteres.',
        ]);

        $credentials = [
            'email'    => $this->loginEmail,
            'password' => $this->loginPassword,
        ];

        if (! Auth::guard('customer')->attempt($credentials)) {
            $this->errorMessage = 'E-mail ou senha incorretos.';
            return;
        }

        $this->afterAuth();
    }

    public function attemptRegister(): void
    {
        $this->errorMessage = null;

        $rules = [
            'registerType'                 => 'required|in:pf,pj',
            'registerEmail'                => 'required|email|max:200|unique:customers,email',
            'registerPassword'             => 'required|string|min:8',
            'registerPasswordConfirmation' => 'required|same:registerPassword',
        ];

        $messages = [
            'registerType.required'                 => 'Selecione o tipo de cadastro.',
            'registerEmail.required'                => 'Informe seu e-mail.',
            'registerEmail.email'                   => 'E-mail inválido.',
            'registerEmail.unique'                  => 'Este e-mail já está cadastrado. Faça login.',
            'registerPassword.required'             => 'Informe uma senha.',
            'registerPassword.min'                  => 'A senha deve ter pelo menos 8 caracteres.',
            'registerPasswordConfirmation.required' => 'Confirme sua senha.',
            'registerPasswordConfirmation.same'     => 'As senhas não conferem.',
        ];

        if ($this->registerType === 'pf') {
            $rules['registerName'] = 'required|string|min:3|max:120';
            $rules['registerCpf']  = 'nullable|string|max:14';
            $messages['registerName.required'] = 'Informe seu nome completo.';
        } else {
            $rules['registerCompanyName']      = 'required|string|min:2|max:200';
            $rules['registerResponsibleName']  = 'required|string|min:3|max:120';
            $rules['registerCnpj']             = 'nullable|string|max:18';
            $messages['registerCompanyName.required']     = 'Informe a razão social.';
            $messages['registerResponsibleName.required'] = 'Informe o nome do responsável.';
        }

        $this->validate($rules, $messages);

        $data = [
            'type'     => $this->registerType,
            'email'    => $this->registerEmail,
            'mobile'   => $this->registerMobile ?: null,
            'password' => Hash::make($this->registerPassword),
            'status'   => 'active',
        ];

        if ($this->registerType === 'pf') {
            $data['name']       = $this->registerName;
            $data['cpf']        = $this->registerCpf ?: null;
            $data['birth_date'] = $this->registerBirthDate ?: null;
        } else {
            $data['name']               = $this->registerResponsibleName;
            $data['company_name']       = $this->registerCompanyName;
            $data['responsible_name']   = $this->registerResponsibleName;
            $data['cnpj']               = $this->registerCnpj ?: null;
        }

        $customer = Customer::create($data);

        Auth::guard('customer')->login($customer);

        try {
            Log::channel('email')->info('Checkout: disparando CustomerWelcome', ['email' => $customer->email]);
            Mail::to($customer->email)->send(new CustomerWelcome($customer));
        } catch (\Throwable $e) {
            Log::channel('email')->error('Checkout: FALHA ao enviar CustomerWelcome', [
                'email'     => $customer->email,
                'exception' => $e->getMessage(),
            ]);
        }

        $this->afterAuth();
    }

    private function afterAuth(): void
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        // Mescla o carrinho anônimo da sessão com o carrinho do cliente
        app(CartService::class)->mergeSessionIntoCustomer($customer->id);

        // Redirect completo para renovar o CSRF token após autenticação.
        // Sem isso, o token da página fica desincronizado com a sessão
        // e qualquer requisição Livewire posterior retorna 419.
        $this->redirect(route('checkout.index'), navigate: false);
    }

    // ── Etapa 1: Endereço ─────────────────────────────────────────────────

    public function selectSavedAddress(int $addressId): void
    {
        $this->selectedAddressId = $addressId;
        $this->editingAddressId  = null;
        $this->useNewAddress     = false;

        $address = CustomerAddress::find($addressId);

        if ($address && $address->customer_id === $this->customer?->id) {
            $this->fillFromAddress($address);
        }
    }

    public function editSavedAddress(int $addressId): void
    {
        $address = CustomerAddress::where('id', $addressId)
            ->where('customer_id', $this->customer?->id)
            ->first();

        if (! $address) {
            return;
        }

        $this->editingAddressId  = $addressId;
        $this->selectedAddressId = null;
        $this->useNewAddress     = true;

        $this->addrLabel      = $address->label ?? '';
        $this->addrName       = $this->customer?->display_name ?? '';
        $this->addrPhone      = $this->customer?->mobile ?? $this->customer?->phone ?? '';
        $this->addrZip        = $address->cep;
        $this->addrStreet     = $address->street;
        $this->addrNumber     = $address->number;
        $this->addrComplement = $address->complement ?? '';
        $this->addrDistrict   = $address->district;
        $this->addrCity       = $address->city;
        $this->addrState      = $address->state;
    }

    public function switchToNewAddress(): void
    {
        $this->selectedAddressId = null;
        $this->editingAddressId  = null;
        $this->useNewAddress     = true;
        $this->resetAddr();
    }

    public function lookupZip(): void
    {
        $zip = preg_replace('/\D/', '', $this->addrZip);

        if (strlen($zip) !== 8) {
            return;
        }

        $response = \Illuminate\Support\Facades\Http::get("https://viacep.com.br/ws/{$zip}/json/");

        if ($response->successful() && ! $response->json('erro')) {
            $data = $response->json();
            $this->addrStreet   = $data['logradouro'] ?? '';
            $this->addrDistrict = $data['bairro'] ?? '';
            $this->addrCity     = $data['localidade'] ?? '';
            $this->addrState    = $data['uf'] ?? '';
        }
    }

    public function goToShipping(): void
    {
        $this->errorMessage = null;

        $this->validate([
            'addrName'     => 'required|string|min:3|max:120',
            'addrZip'      => ['required', 'regex:/^\d{5}-?\d{3}$/'],
            'addrStreet'   => 'required|string|min:3|max:200',
            'addrNumber'   => 'required|string|max:20',
            'addrDistrict' => 'required|string|max:100',
            'addrCity'     => 'required|string|max:100',
            'addrState'    => 'required|string|size:2',
        ], [
            'addrName.required'     => 'Informe o nome do destinatário.',
            'addrZip.required'      => 'Informe o CEP.',
            'addrZip.regex'         => 'CEP inválido.',
            'addrStreet.required'   => 'Informe o logradouro.',
            'addrNumber.required'   => 'Informe o número.',
            'addrDistrict.required' => 'Informe o bairro.',
            'addrCity.required'     => 'Informe a cidade.',
            'addrState.required'    => 'Informe o estado.',
        ]);

        // Persiste o endereço na conta do cliente
        if ($customer = $this->customer) {
            $addrData = [
                'label'      => $this->addrLabel ?: null,
                'cep'        => preg_replace('/\D/', '', $this->addrZip),
                'street'     => $this->addrStreet,
                'number'     => $this->addrNumber,
                'complement' => $this->addrComplement ?: null,
                'district'   => $this->addrDistrict,
                'city'       => $this->addrCity,
                'state'      => strtoupper($this->addrState),
            ];

            if ($this->editingAddressId) {
                // Atualiza endereço existente
                CustomerAddress::where('id', $this->editingAddressId)
                    ->where('customer_id', $customer->id)
                    ->update($addrData);

                $this->selectedAddressId = $this->editingAddressId;
                $this->editingAddressId  = null;
                $this->useNewAddress     = false;

            } elseif ($this->useNewAddress) {
                // Cria novo endereço
                $isFirst    = $customer->addresses()->count() === 0;
                $newAddress = $customer->addresses()->create(
                    array_merge($addrData, ['country' => 'BR', 'is_default' => $isFirst])
                );

                $this->selectedAddressId = $newAddress->id;
                $this->useNewAddress     = false;
            }
        }

        // Reseta opções anteriores e sinaliza loading — o cálculo acontece
        // via wire:init="loadShipping" após a tela do step 2 renderizar.
        $this->shippingOptions       = [];
        $this->selectedShippingIndex = null;
        $this->loadingShipping       = true;
        $this->step                  = 2;
        $this->dispatch('checkout-step-changed', step: 2);
    }

    // ── Etapa 2: Frete ────────────────────────────────────────────────────

    public function goToPayment(): void
    {
        if ($this->selectedShippingIndex === null) {
            $this->addError('selectedShippingIndex', 'Selecione uma opção de frete.');
            return;
        }

        $this->step = 3;
        $this->dispatch('checkout-step-changed', step: 3);
    }

    // ── Etapa 3: Pagamento ────────────────────────────────────────────────

    public function goToReview(): void
    {
        $this->errorMessage = null;

        if ($this->paymentMethod === 'credit_card') {
            $this->validate([
                'cardHolder' => 'required|string|min:3',
                'cardNumber' => 'required|digits_between:13,19',
                'cardExpiry' => ['required', 'regex:/^\d{2}\/\d{2}$/'],
                'cardCvv'    => 'required|digits_between:3,4',
            ], [
                'cardHolder.required'      => 'Informe o nome no cartão.',
                'cardNumber.required'      => 'Informe o número do cartão.',
                'cardNumber.digits_between' => 'Número de cartão inválido.',
                'cardExpiry.required'      => 'Informe a validade (MM/AA).',
                'cardExpiry.regex'         => 'Validade inválida. Use MM/AA.',
                'cardCvv.required'         => 'Informe o código de segurança.',
            ]);
        }

        $this->step = 4;
        $this->dispatch('checkout-step-changed', step: 4);
    }

    // ── Etapa 4: Confirmar pedido ─────────────────────────────────────────

    public function placeOrder(): void
    {
        $this->errorMessage = null;
        $this->processing   = true;

        try {
            $cart     = $this->cart;
            $customer = $this->customer;
            $shipping = $this->selectedShipping;

            if (! $customer) {
                $this->step = 0;
                return;
            }

            if (! $shipping) {
                $this->errorMessage = 'Selecione uma opção de frete.';
                return;
            }

            // 0. Verifica se o gateway está configurado ANTES de criar o pedido
            $asaas = app(AsaasService::class);
            if (! $asaas->isConfigured()) {
                $this->errorMessage = 'O gateway de pagamento não está configurado. Entre em contato com a loja.';
                Log::error('Checkout: tentativa de finalizar pedido sem token Asaas configurado');
                return;
            }

            $address = [
                'name'       => $this->addrName,
                'phone'      => $this->addrPhone,
                'zip'        => preg_replace('/\D/', '', $this->addrZip),
                'street'     => $this->addrStreet,
                'number'     => $this->addrNumber,
                'complement' => $this->addrComplement,
                'district'   => $this->addrDistrict,
                'city'       => $this->addrCity,
                'state'      => strtoupper($this->addrState),
            ];

            // Calcula descontos/acréscimos de pagamento
            $calc = app(PaymentCalculator::class);
            $pixDiscount = 0.0;
            if ($this->paymentMethod === 'pix') {
                $pixDiscount = $calc->pixSavings($this->total);
            }

            // 1. Cria o pedido no banco
            $order = app(OrderService::class)->createFromCart(
                cart:          $cart,
                address:       $address,
                shipping:      $shipping,
                paymentMethod: $this->paymentMethod,
                customer:      $customer,
                guest:         [],
                notes:         $this->notes ?: null,
                pixDiscount:   $pixDiscount,
            );

            // 2. Processa o pagamento no Asaas
            $asaasCustomerId = $asaas->findOrCreateCustomer($order);

            if (! $asaasCustomerId) {
                // Falha ao criar/localizar cliente no Asaas (ex: timeout, API fora do ar)
                Log::error('Checkout: falha ao obter asaasCustomerId', ['order' => $order->order_number]);
                $this->errorMessage = "Falha ao conectar com o gateway de pagamento. "
                    . "Seu pedido #{$order->order_number} foi registrado — entre em contato com a loja para efetuar o pagamento.";
                return;
            }

            if ($this->paymentMethod === 'pix') {
                $paymentData = $asaas->createPixPayment($order, $asaasCustomerId);
            } else {
                [$month, $year] = explode('/', $this->cardExpiry);

                $installmentOpts   = $calc->installmentOptions($this->total);
                $chosenInstallment = collect($installmentOpts)->firstWhere('value', $this->installments);
                $installmentValue  = $chosenInstallment['installment_value'] ?? round($this->total / $this->installments, 2);
                $interestFree      = $chosenInstallment['interest_free'] ?? true;

                $paymentData = $asaas->createCreditCardPayment(
                    order:           $order,
                    asaasCustomerId: $asaasCustomerId,
                    card: [
                        'holderName'  => $this->cardHolder,
                        'number'      => $this->cardNumber,
                        'expiryMonth' => $month,
                        'expiryYear'  => '20' . $year,
                        'ccv'         => $this->cardCvv,
                    ],
                    installments: $this->installments,
                );

                if ($paymentData) {
                    $paymentData['installments']        = $this->installments;
                    $paymentData['installment_value']   = $installmentValue;
                    $paymentData['interest_free']       = $interestFree;
                    $paymentData['total_with_interest'] = round($installmentValue * $this->installments, 2);
                }
            }

            if (! $paymentData) {
                // Asaas retornou erro ao criar o pagamento
                Log::error('Checkout: Asaas não retornou payment_data', ['order' => $order->order_number]);
                $this->errorMessage = "Falha ao gerar o pagamento. "
                    . "Seu pedido #{$order->order_number} foi registrado — entre em contato com a loja.";
                return;
            }

            app(OrderService::class)->attachPaymentData(
                order:     $order,
                paymentId: $paymentData['payment_id'],
                data:      $paymentData,
            );

            if (
                $this->paymentMethod === 'credit_card'
                && in_array($paymentData['status'] ?? '', ['CONFIRMED', 'RECEIVED'])
            ) {
                app(OrderService::class)->markAsPaid(
                    order:     $order,
                    paymentId: $paymentData['payment_id'],
                );
            }

            // 3. Redireciona para a confirmação
            $this->redirect(route('order.confirmation', $order->order_number), navigate: false);

        } catch (\App\Exceptions\CheckoutException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Checkout error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->errorMessage = 'Ocorreu um erro inesperado ao processar seu pedido. Por favor, tente novamente.';
        } finally {
            $this->processing = false;
        }
    }

    // ── Navegação ─────────────────────────────────────────────────────────

    public function backTo(int $step): void
    {
        $this->step = max(1, $step);
        $this->dispatch('checkout-step-changed', step: $this->step);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function prefillAddress(Customer $customer): void
    {
        $default = $customer->addresses()->where('is_default', true)->first()
            ?? $customer->addresses()->first();

        if ($default) {
            $this->selectedAddressId = $default->id;
            $this->fillFromAddress($default);
        } else {
            $this->useNewAddress = true;
            $this->addrName      = $customer->display_name;
            $this->addrPhone     = $customer->mobile ?? $customer->phone ?? '';
        }
    }

    private function fillFromAddress(CustomerAddress $address): void
    {
        $this->addrName       = $this->customer?->display_name ?? '';
        $this->addrZip        = $address->cep;
        $this->addrStreet     = $address->street;
        $this->addrNumber     = $address->number;
        $this->addrComplement = $address->complement ?? '';
        $this->addrDistrict   = $address->district;
        $this->addrCity       = $address->city;
        $this->addrState      = $address->state;
    }

    private function resetAddr(): void
    {
        $this->addrLabel      = '';
        $this->addrName       = '';
        $this->addrPhone      = '';
        $this->addrZip        = '';
        $this->addrStreet     = '';
        $this->addrNumber     = '';
        $this->addrComplement = '';
        $this->addrDistrict   = '';
        $this->addrCity       = '';
        $this->addrState      = '';
    }

    // ── Render ────────────────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.shop.checkout-page');
    }
}

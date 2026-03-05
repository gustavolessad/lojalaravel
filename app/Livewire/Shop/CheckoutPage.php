<?php

namespace App\Livewire\Shop;

use App\Mail\CustomerWelcome;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Rules\ValidCpf;
use App\Rules\ValidCnpj;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\PaymentPayload;
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
    public string $authMode  = 'login';    // 'login' | 'register'
    public string $authStep  = 'email';    // 'email' | 'login' | 'register'
    public string $authEmail = '';         // e-mail digitado na primeira tela

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

    /**
     * Valor dos juros de parcelamento quando o cliente escolhe parcelas com juros.
     * Retorna 0 quando sem juros ou método != cartão.
     */
    #[Computed]
    public function cardInterestAmount(): float
    {
        if ($this->paymentMethod !== 'credit_card' || $this->installments <= 1) {
            return 0.0;
        }

        $chosenOpt = collect($this->installmentOptions)->firstWhere('value', $this->installments);

        if (! $chosenOpt || ($chosenOpt['interest_free'] ?? true)) {
            return 0.0;
        }

        $actualAmount = round(($chosenOpt['installment_value'] ?? 0) * $this->installments, 2);

        return max(0.0, round($actualAmount - $this->total, 2));
    }

    /**
     * Total final considerando método de pagamento escolhido:
     * PIX → aplica desconto PIX; Cartão com juros → soma os juros.
     */
    #[Computed]
    public function finalTotal(): float
    {
        if ($this->paymentMethod === 'pix') {
            return $this->pixTotal ?? $this->total;
        }

        return round($this->total + $this->cardInterestAmount, 2);
    }

    // ── Etapa 0: Autenticação ─────────────────────────────────────────────

    public function checkEmail(): void
    {
        $this->errorMessage = null;

        $this->validate([
            'authEmail' => 'required|email',
        ], [
            'authEmail.required' => 'Informe seu e-mail.',
            'authEmail.email'    => 'E-mail inválido.',
        ]);

        $exists = Customer::where('email', $this->authEmail)->exists();

        if ($exists) {
            $this->loginEmail = $this->authEmail;
            $this->authStep   = 'login';
            $this->authMode   = 'login';
        } else {
            $this->registerEmail = $this->authEmail;
            $this->authStep      = 'register';
            $this->authMode      = 'register';
        }

        $this->resetValidation();
    }

    public function backToEmailCheck(): void
    {
        $this->authStep = 'email';
        $this->errorMessage = null;
        $this->loginPassword = '';
        $this->resetValidation();
    }

    public function switchAuthMode(string $mode): void
    {
        $this->authMode = $mode;
        $this->authStep = $mode === 'login' ? 'login' : 'register';
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

        // Strip máscaras antes de validar/salvar
        $cpf    = preg_replace('/\D/', '', $this->registerCpf);
        $cnpj   = preg_replace('/\D/', '', $this->registerCnpj);
        $mobile = preg_replace('/\D/', '', $this->registerMobile);

        if ($this->registerType === 'pf') {
            $rules['registerName'] = 'required|string|min:3|max:120';
            $rules['registerCpf']  = ['nullable', 'digits:11', new ValidCpf(), 'unique:customers,cpf'];
            $messages['registerName.required']   = 'Informe seu nome completo.';
            $messages['registerCpf.digits']      = 'O CPF deve conter 11 dígitos.';
            $messages['registerCpf.unique']      = 'Este CPF já está cadastrado.';
        } else {
            $rules['registerCompanyName']      = 'required|string|min:2|max:200';
            $rules['registerResponsibleName']  = 'required|string|min:3|max:120';
            $rules['registerCnpj']             = ['nullable', 'digits:14', new ValidCnpj(), 'unique:customers,cnpj'];
            $messages['registerCompanyName.required']     = 'Informe a razão social.';
            $messages['registerResponsibleName.required'] = 'Informe o nome do responsável.';
            $messages['registerCnpj.digits']              = 'O CNPJ deve conter 14 dígitos.';
            $messages['registerCnpj.unique']              = 'Este CNPJ já está cadastrado.';
        }

        // Substitui temporariamente os valores mascarados pelos limpos para validação
        $this->registerCpf    = $cpf;
        $this->registerCnpj   = $cnpj;
        $this->registerMobile = $mobile;

        $this->validate($rules, $messages);

        $data = [
            'type'     => $this->registerType,
            'email'    => $this->registerEmail,
            'mobile'   => $mobile ?: null,
            'password' => Hash::make($this->registerPassword),
            'status'   => 'active',
        ];

        if ($this->registerType === 'pf') {
            $data['name']       = $this->registerName;
            $data['cpf']        = $cpf ?: null;
            // Converte birth_date de DD/MM/AAAA → Y-m-d antes de persistir
            $birthDate = null;
            if ($this->registerBirthDate) {
                $d = \DateTime::createFromFormat('d/m/Y', $this->registerBirthDate);
                $birthDate = $d ? $d->format('Y-m-d') : null;
            }
            $data['birth_date'] = $birthDate;
        } else {
            $data['name']               = $this->registerResponsibleName;
            $data['company_name']       = $this->registerCompanyName;
            $data['responsible_name']   = $this->registerResponsibleName;
            $data['cnpj']               = $cnpj ?: null;
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
                CustomerAddress::where('id', $this->editingAddressId)
                    ->where('customer_id', $customer->id)
                    ->update($addrData);

                $this->selectedAddressId = $this->editingAddressId;
                $this->editingAddressId  = null;
                $this->useNewAddress     = false;

            } elseif ($this->useNewAddress) {
                $isFirst    = $customer->addresses()->count() === 0;
                $newAddress = $customer->addresses()->create(
                    array_merge($addrData, ['country' => 'BR', 'is_default' => $isFirst])
                );

                $this->selectedAddressId = $newAddress->id;
                $this->useNewAddress     = false;
            }
        }

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

    // Remove espaços da máscara do cartão ao sincronizar com Livewire
    public function updatedCardNumber(): void
    {
        $this->cardNumber = preg_replace('/\D/', '', $this->cardNumber);
    }

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
                'cardHolder.required'       => 'Informe o nome no cartão.',
                'cardNumber.required'       => 'Informe o número do cartão.',
                'cardNumber.digits_between' => 'Número de cartão inválido.',
                'cardExpiry.required'       => 'Informe a validade (MM/AA).',
                'cardExpiry.regex'          => 'Validade inválida. Use MM/AA.',
                'cardCvv.required'          => 'Informe o código de segurança.',
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
            $customer = $this->customer;

            if (! $customer) {
                $this->step = 0;
                return;
            }

            $paymentManager = app(PaymentManager::class);

            if (! $paymentManager->isConfigured()) {
                $this->errorMessage = 'O gateway de pagamento não está configurado. Entre em contato com a loja.';
                Log::error('Checkout: tentativa de finalizar pedido sem token de pagamento configurado');
                return;
            }

            $shipping = $this->selectedShipping;

            if (! $shipping) {
                $this->errorMessage = 'Selecione uma opção de frete.';
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

            $calc        = app(PaymentCalculator::class);
            $orderService = app(OrderService::class);

            // ─────────────────────────────────────────────────────────────
            // CARTÃO: cobrar ANTES de criar o pedido.
            // O carrinho permanece intacto até a aprovação.
            // ─────────────────────────────────────────────────────────────
            if ($this->paymentMethod === 'credit_card') {

                $orderTotal        = $this->total;
                $installmentOpts   = $calc->installmentOptions($orderTotal);
                $chosenInstallment = collect($installmentOpts)->firstWhere('value', $this->installments);
                $installmentValue  = $chosenInstallment['installment_value'] ?? round($orderTotal / $this->installments, 2);
                $interestFree      = $chosenInstallment['interest_free'] ?? true;

                // Valor real cobrado no cartão: sem juros = total original;
                // com juros = installmentValue × parcelas (juros calculados na loja,
                // Asaas usa installmentValue×n como total — não aplica juros próprios).
                $actualAmount = $interestFree
                    ? $orderTotal
                    : round($installmentValue * $this->installments, 2);
                $cardInterest = round($actualAmount - $orderTotal, 2);

                // Pré-gera o número do pedido para usar na descrição da cobrança.
                // Asaas não permite editar cobranças já aprovadas (CONFIRMED), então
                // precisamos do número antes de criar a charge.
                $preOrderNumber = Order::generateOrderNumber();

                $expiryParts = explode('/', $this->cardExpiry);
                $month = $expiryParts[0] ?? '';
                $year  = $expiryParts[1] ?? '';

                $payload = new PaymentPayload(
                    method:               'credit_card',
                    amount:               $actualAmount,
                    description:          "Pedido #{$preOrderNumber}",
                    reference:            $preOrderNumber,
                    customerName:         $customer->display_name,
                    customerEmail:        $customer->email,
                    customerCpfCnpj:      $customer->cpf ?? $customer->cnpj,
                    customerPhone:        preg_replace('/\D/', '', $this->addrPhone),
                    cardHolder:           $this->cardHolder,
                    cardNumber:           $this->cardNumber,
                    cardExpiryMonth:      $month,
                    cardExpiryYear:       '20' . $year,
                    cardCvv:              $this->cardCvv,
                    billingPostalCode:    preg_replace('/\D/', '', $this->addrZip),
                    billingAddressNumber: $this->addrNumber,
                    installments:         $this->installments,
                    installmentValue:     $installmentValue,
                    interestFree:         $interestFree,
                );

                $result = $paymentManager->createCharge($payload);

                if (! $result->isApproved()) {
                    $lastError  = $paymentManager->getLastError();
                    $errorDesc  = $lastError['errors'][0]['description'] ?? null;
                    $this->errorMessage = $errorDesc ?? 'Verifique os dados do cartão e tente novamente.';
                    $this->step = 3;
                    $this->dispatch('checkout-step-changed', step: 3);
                    return;
                }

                // Pagamento aprovado → criar pedido, limpar carrinho, decrementar estoque
                $order = $orderService->createFromCart(
                    cart:         $this->cart,
                    address:      $address,
                    shipping:     $shipping,
                    paymentMethod: 'credit_card',
                    customer:     $customer,
                    notes:        $this->notes ?: null,
                    pixDiscount:  0.0,
                    cardInterest: $cardInterest,
                    orderNumber:  $preOrderNumber,
                );

                $storageData = $result->toStorageArray($this->installments, $installmentValue, $interestFree);
                if ($cardInterest > 0) {
                    $storageData['card_interest'] = $cardInterest;
                }

                $orderService->attachPaymentData(
                    order:     $order,
                    paymentId: $result->transactionId,
                    data:      $storageData,
                );

                $orderService->markAsPaid(
                    order:     $order,
                    paymentId: $result->transactionId,
                );

            // ─────────────────────────────────────────────────────────────
            // PIX: criar pedido primeiro, depois gerar QR Code.
            // Pedido expira em 24h se não pago (cancelado por comando artisan).
            // ─────────────────────────────────────────────────────────────
            } else {

                $pixDiscount = $calc->pixSavings($this->total);

                $order = $orderService->createFromCart(
                    cart:          $this->cart,
                    address:       $address,
                    shipping:      $shipping,
                    paymentMethod: 'pix',
                    customer:      $customer,
                    notes:         $this->notes ?: null,
                    pixDiscount:   $pixDiscount,
                );

                $payload = new PaymentPayload(
                    method:          'pix',
                    amount:          (float) $order->total,
                    description:     "Pedido #{$order->order_number}",
                    reference:       (string) $order->id,
                    customerName:    $order->buyer_name,
                    customerEmail:   $order->buyer_email,
                    customerCpfCnpj: $customer->cpf ?? $customer->cnpj,
                    customerPhone:   preg_replace('/\D/', '', $order->shipping_phone ?? ''),
                );

                $result = $paymentManager->createCharge($payload);

                $orderService->attachPaymentData(
                    order:     $order,
                    paymentId: $result->transactionId,
                    data:      $result->toStorageArray(),
                );
            }

            $this->redirect(route('order.confirmation', $order->order_number), navigate: false);

        } catch (\App\Exceptions\CheckoutException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\RuntimeException $e) {
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

<?php

declare(strict_types=1);

return [
    'form' => [
        'title' => 'Título',
        'url' => 'URL',
        'linkable_type' => 'Tipo',
        'linkable_id' => 'ID',
    ],
    'resource' => [
        'name' => [
            'label' => 'Nome',
        ],
        'locations' => [
            'label' => 'Locais',
            'empty' => 'Não atribuído',
        ],
        'items' => [
            'label' => 'Itens',
        ],
        'is_visible' => [
            'label' => 'Visibilidade',
            'visible' => 'Visível',
            'hidden' => 'Oculto',
        ],
    ],
    'actions' => [
        'add' => [
            'label' => 'Adicionar ao Menu',
        ],
        'indent' => 'Recuar',
        'unindent' => 'Remover recuo',
        'locations' => [
            'label' => 'Locais',
            'heading' => 'Gerenciar Locais',
            'description' => 'Escolha qual menu aparece em cada local.',
            'submit' => 'Atualizar',
            'form' => [
                'location' => [
                    'label' => 'Local',
                ],
                'menu' => [
                    'label' => 'Menu atribuído',
                ],
            ],
            'empty' => [
                'heading' => 'Nenhum local registrado',
            ],
        ],
    ],
    'items' => [
        'expand' => 'Expandir',
        'collapse' => 'Recolher',
        'empty' => [
            'heading' => 'Não há itens neste menu.',
        ],
    ],
    'custom_link' => 'Link personalizado',
    'custom_text' => 'Texto personalizado',
    'open_in' => [
        'label' => 'Abrir em',
        'options' => [
            'self' => 'Mesma aba',
            'blank' => 'Nova aba',
            'parent' => 'Aba pai',
            'top' => 'Aba superior',
        ],
    ],
    'notifications' => [
        'created' => [
            'title' => 'Link criado',
        ],
        'locations' => [
            'title' => 'Locais do menu atualizados',
        ],
    ],
    'panel' => [
        'empty' => [
            'heading' => 'Nenhum item encontrado',
            'description' => 'Não há itens neste menu.',
        ],
        'pagination' => [
            'previous' => 'Anterior',
            'next' => 'Próximo',
        ],
    ],
];

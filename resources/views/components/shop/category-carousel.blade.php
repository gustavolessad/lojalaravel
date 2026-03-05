@php
    $categories = \App\Models\Category::where('show_on_home', true)
        ->with('media')
        ->orderBy('order')
        ->orderBy('name')
        ->get();
@endphp

@if ($categories->isNotEmpty())
<section class="py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900 text-center">Compre por categorias</h2>
        <p class="text-center">Principais temas e categorias de quadros exclusivos</p>
        <div class="flex gap-2">
            <button class="cat-prev w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button class="cat-next w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="swiper category-carousel-swiper" x-data x-init="
        new Swiper($el, {
            modules: [SwiperModules.Navigation, SwiperModules.FreeMode],
            slidesPerView: 1,
            spaceBetween: 16,
            freeMode: true,
            breakpoints: {
                768: { slidesPerView: 3 },
                1024: { slidesPerView: 5 },
            },
            navigation: {
                nextEl: $el.closest('section').querySelector('.cat-next'),
                prevEl: $el.closest('section').querySelector('.cat-prev'),
            },
        })
    ">
        <div class="swiper-wrapper">
            @foreach ($categories as $category)
                <div class="swiper-slide">
                    <a href="{{ $category->url }}"
                       class="flex flex-col items-center gap-3 group">
                        <div class="w-full aspect-square object-cover rounded-xl overflow-hidden flex items-center justify-center border border-transparent group-hover:border-gray-900 transition">
                            @if ($category->getFirstMediaUrl('icon', 'optimized'))
                                <img src="{{ $category->getFirstMediaUrl('icon', 'optimized') }}"
                                     alt="{{ $category->name }}"
                                     class="w-full h-full object-cover"
                                     loading="lazy">
                            @else
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            @endif
                        </div>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-gray-900 text-center leading-tight transition line-clamp-2">
                            {{ $category->name }}
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

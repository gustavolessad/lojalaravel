@php
    $banners = \App\Models\Banner::where('group', 'principal')
        ->where('active', true)
        ->orderBy('order')
        ->get();
@endphp

@if ($banners->isNotEmpty())
<div class="swiper banner-principal-swiper" x-data x-init="
    new Swiper($el, {
        modules: [SwiperModules.Navigation, SwiperModules.Pagination, SwiperModules.Autoplay],
        loop: {{ $banners->count() > 1 ? 'true' : 'false' }},
        autoplay: {{ $banners->count() > 1 ? '{ delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true }' : 'false' }},
        navigation: {
            nextEl: $el.querySelector('.swiper-button-next'),
            prevEl: $el.querySelector('.swiper-button-prev'),
        },
        pagination: {
            el: $el.querySelector('.swiper-pagination'),
            clickable: true,
        },
    })
">
    <div class="swiper-wrapper">
        @foreach ($banners as $index => $banner)
            <div class="swiper-slide">
                @if ($banner->link)
                    <a
                        href="{{ $banner->link }}"
                        @if ($banner->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                        class="block w-full"
                    >
                        <picture class="block w-full">
                            <source media="(max-width: 767px)" srcset="{{ $banner->mobileUrl() }}">
                            <img
                                src="{{ $banner->desktopUrl() }}"
                                alt="{{ $banner->title }}"
                                class="w-full h-auto"
                                @if ($index === 0) loading="eager" @else loading="lazy" @endif
                            >
                        </picture>
                    </a>
                @else
                    <picture class="block w-full">
                        <source media="(max-width: 767px)" srcset="{{ $banner->mobileUrl() }}">
                        <img
                            src="{{ $banner->desktopUrl() }}"
                            alt="{{ $banner->title }}"
                            class="w-full h-auto"
                            @if ($index === 0) loading="eager" @else loading="lazy" @endif
                        >
                    </picture>
                @endif
            </div>
        @endforeach
    </div>

    @if ($banners->count() > 1)
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-pagination"></div>
    @endif
</div>
@endif

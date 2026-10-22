@use(Illuminate\Support\Facades\Vite)

<style>

    :root{
        --rank-pos: 225px;
        --rank-gap: 50px;
    }

    .overlay-card {
        position: relative;
        width: 422px;
        height: 96px;
        display: flex;
        align-items: center;
        padding: 18px 17px;
        gap: 18px;
        background: #fff;
        border: 3px solid #c1006e;
        border-radius: 128px;
        box-sizing: border-box;
        left: calc(50% - (422px / 2 ));
    }

    .overlay-avatar-wrap {
        flex-shrink: 0;
        width: 62px;
        height: 62px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .overlay-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
        border-radius: 50%;
        border: 4px solid #E91E8C;
    }

    .overlay-text-container {
        position: relative;
        min-width: 0;
        flex: 1;
        height: 100%;
    }

    .overlay-user {
        position: absolute;
        top: -6px;
        left: 0;
        right: 0;
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        font-size: 28px;
        line-height: 1.1;
        color: #2c225c;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .overlay-details-container {
        position: absolute;
        top: 26px;
        left: 0;
        right: 0;
        display: flex;
        align-items: flex-start;
        min-width: 0;
    }

    .overlay-details {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 20px;
        margin-left: 4px;
        font-style: italic;
        line-height: 1.1;
        color: #c50054;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
        flex: 1;
    }

    #overlay-topText  {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 70px;
        font-style: italic;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 1px;
        min-width: 0;
    }

    #overlay-topText-wrap  {
        position: absolute;
        top: 100px;
        -webkit-text-stroke: 2px #000;
        paint-order: stroke fill;
        filter: drop-shadow(0 0 1px #000) drop-shadow(0 0 1px #000);
        align-items: center;
        text-align: center;

        width: 100%;
    }

    .icon {
        display: none;
        position: absolute;
        width: 75px;
        height: 75px;
        flex-shrink: 0;
        color: #fff;
        filter: drop-shadow(0 0 3px #000) drop-shadow(0 0 3px #000);
        rotate: 338deg;
        translate: -26px -48px;
    }

    .overlay-card#place-1 .icon {
        display: block;
        color: #ffd230;
    }

    .overlay-card#place-2 .icon {
        display: block;
        color: #a1a1a1;
    }

    .overlay-card#place-3 .icon {
        display: block;
        color: #7b3306;
    }

    #place-1 {
        top: calc( var(--rank-pos) + ( var(--rank-gap) * 0 ) );
        left: calc(50% - (422px / 2 ));
    }

    #place-2 {
        top: calc( var(--rank-pos) + ( var(--rank-gap) * 1 ) );
        left: calc(50% - (422px / 2 ));
    }

    #place-3 {
        top: calc( var(--rank-pos) + ( var(--rank-gap) * 2 ) );
        left: calc(50% - (422px / 2 ));
    }

    #place-4 {
        top: calc( var(--rank-pos) + ( var(--rank-gap) * 3 ) );
        left: calc(50% - (422px / 2 ));
    }

    #place-5 {
        top: calc( var(--rank-pos) + ( var(--rank-gap) * 4 ) );
        left: calc(50% - (422px / 2 ));
    }

    #background {
        position: absolute;
        top: 0px;
        left: 0px;
    }
</style>

<div id="background" style="display: none;">
    <img src="{{ Vite::asset('resources/images/png/top-overlay-bg.png') }}" alt="">
</div>

<div id="overlay-topText-wrap">
    <div id="overlay-topText"></div>
</div>

<div class="overlay-card" id="place-1" style="display: none;">
    <x-lucide-crown class="icon"/>
    <div class="overlay-avatar-wrap">
        <img class="overlay-avatar-img" alt="" crossorigin="anonymous" />
        <img src="{{ Vite::asset('resources/images/svg/logo-light.svg') }}" alt="" class="overlay-avatar-fallback" />
    </div>
    <div class="overlay-text-container">
        <div class="overlay-user"></div>
        <div class="overlay-details-container">
            <div class="overlay-details"></div>
        </div>
    </div>
</div>

<div class="overlay-card" id="place-2" style="display: none;">
    <x-lucide-crown class="icon"/>
    <div class="overlay-avatar-wrap">
        <img class="overlay-avatar-img" alt="" crossorigin="anonymous" />
        <img src="{{ Vite::asset('resources/images/svg/logo-light.svg') }}" alt="" class="overlay-avatar-fallback" />
    </div>
    <div class="overlay-text-container">
        <div class="overlay-user"></div>
        <div class="overlay-details-container">
            <div class="overlay-details"></div>
        </div>
    </div>
</div>

<div class="overlay-card" id="place-3" style="display: none;">
    <x-lucide-crown class="icon"/>
    <div class="overlay-avatar-wrap">
        <img class="overlay-avatar-img" alt="" crossorigin="anonymous" />
        <img src="{{ Vite::asset('resources/images/svg/logo-light.svg') }}" alt="" class="overlay-avatar-fallback" />
    </div>
    <div class="overlay-text-container">
        <div class="overlay-user"></div>
        <div class="overlay-details-container">
            <div class="overlay-details"></div>
        </div>
    </div>
</div>

<div class="overlay-card" id="place-4" style="display: none;">
    <div class="overlay-avatar-wrap">
        <img class="overlay-avatar-img" alt="" crossorigin="anonymous" />
        <img src="{{ Vite::asset('resources/images/svg/logo-light.svg') }}" alt="" class="overlay-avatar-fallback" />
    </div>
    <div class="overlay-text-container">
        <div class="overlay-user"></div>
        <div class="overlay-details-container">
            <div class="overlay-details"></div>
        </div>
    </div>
</div>

<div class="overlay-card" id="place-5" style="display: none;">
    <div class="overlay-avatar-wrap">
        <img class="overlay-avatar-img" alt="" crossorigin="anonymous" />
        <img src="{{ Vite::asset('resources/images/svg/logo-light.svg') }}" alt="" class="overlay-avatar-fallback" />
    </div>
    <div class="overlay-text-container">
        <div class="overlay-user"></div>
        <div class="overlay-details-container">
            <div class="overlay-details"></div>
        </div>
    </div>
</div>

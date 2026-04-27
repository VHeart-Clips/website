<style>
    #overlay-card {
        position: absolute;
        top: 11px;
        left: 18px;
        width: 422px;
        height: 96px;
        display: flex;
        align-items: center;
        padding: 18px 17px;
        gap: 18px;
        background: #fff;
        border: 3px solid #c1006e;
        border-radius: 128px;
        overflow: hidden;
        box-sizing: border-box;
    }

    #overlay-avatar-wrap {
        flex-shrink: 0;
        width: 62px;
        height: 62px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #overlay-avatar-img {
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

    #overlay-broadcaster {
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

    #overlay-category-container {
        position: absolute;
        top: 26px;
        left: 0;
        right: 0;
        display: flex;
        align-items: flex-start;
        min-width: 0;
    }

    .overlay-divider {
        flex-shrink: 0;
        width: 60px;
        height: 3px;
        margin-top: 12px;
        background: #c1006e;
        border-radius: 2px;
    }

    #overlay-category {
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

    #overlay-labels {
        position: absolute;
        top: calc(96px + 14px);
        left: 30px;
        display: flex;
        align-items: center;
        gap: 0;
        width: 422px;
    }

    #overlay-clipper-wrap,
    #overlay-cutter-wrap {
        display: none;
        align-items: center;
        gap: 2px;
        width: 50%;
        -webkit-text-stroke: 2px #000;
        paint-order: stroke fill;
        filter: drop-shadow(0 0 1px #000) drop-shadow(0 0 1px #000);
    }

    #overlay-clipper,
    #overlay-cutter {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 20px;
        font-style: italic;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 1px;
        min-width: 0;
    }

    .icon {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        color: #fff;
        filter: drop-shadow(0 0 1px #000) drop-shadow(0 0 1px #000);
    }
</style>

<div id="overlay-card">
    <div id="overlay-avatar-wrap">
        <img id="overlay-avatar-img" alt="" crossorigin="anonymous" />
        <img src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/svg/logo-light.svg') }}" alt="" id="overlay-avatar-fallback" />
    </div>
    <div class="overlay-text-container">
        <div id="overlay-broadcaster"></div>
        <div id="overlay-category-container">
            <div class="overlay-divider"></div>
            <div id="overlay-category"></div>
        </div>
    </div>
</div>

<div id="overlay-labels">
    <div id="overlay-clipper-wrap">
        <x-lucide-clapperboard class="icon"/>
        <span id="overlay-clipper"></span>
    </div>
    <div id="overlay-cutter-wrap">
        <x-lucide-scissors class="icon"/>
        <span id="overlay-cutter"></span>
    </div>
</div>

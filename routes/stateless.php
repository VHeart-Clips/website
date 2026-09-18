<?php

declare(strict_types=1);

use App\Http\Controllers\RedirectController;
use Filament\Facades\Filament;
use JustinKluever\DiscordWebhookBuilder\Components\ActionRow;
use JustinKluever\DiscordWebhookBuilder\Components\Button;
use JustinKluever\DiscordWebhookBuilder\Components\Container;
use JustinKluever\DiscordWebhookBuilder\Components\Section;
use JustinKluever\DiscordWebhookBuilder\Components\TextDisplay;
use JustinKluever\DiscordWebhookBuilder\Components\Thumbnail;
use JustinKluever\DiscordWebhookBuilder\Enums\Components\ButtonStyle;
use JustinKluever\DiscordWebhookBuilder\Support\Color;
use JustinKluever\DiscordWebhookBuilder\Support\Components\DiscordEmoji;

Route::domain('go.vheart.net')
    ->group(function () {
        Route::get('{slug?}', RedirectController::class)
            ->name('shorturl.redirect')
            ->where('slug', '.*');
    });

Route::get('embed/discord/default', static function () {
    $description = <<<'MARKDOWN'
        ### [VHeart - Streamer Clip Compilations für den Tierschutz](https://vheart.net)
        VHeart ist ein Zusammenschluss von Cuttern, Streamern und Künstlern, die eine hochwertige Clip-Compilation für den guten Zweck entwickelt haben!

        Wir sammeln Spenden für den [Erlebnishof Gerhardsbrunn](https://erlebnishof-gerhardsbrunn.de). Dieser ist ein Hof, der von Janne Bach und Tierarzt Ingmar Meth im Jahr 2020 gegründet wurde. Er bietet notleidenden Tieren eine liebevolle Heimat, gleichzeitig ist er ein Ort, an dem Besucher Ruhe finden und mit den Tieren in Kontakt kommen können.

        -# Bist du Streamer? Gehe ins Dashboard, um uns die Erlaubnis zu geben, deine Clips zu nutzen.
        MARKDOWN;

    return response()->json(
        [
            'component' => Container::make(
                Section::make(
                    Thumbnail::make(Vite::asset('resources/images/webp/branding/logo-light.webp')),
                    TextDisplay::make($description),
                ),
                ActionRow::make(
                    Button::make()->label('Jetzt Spenden')->emoji(DiscordEmoji::make('💜'))->style(ButtonStyle::Link)->url(route('shorturl.redirect', ['slug' => 'spenden'])),
                    Button::make()->label('Über Uns')->style(ButtonStyle::Link)->url(route('about')),
                    Button::make()->label('Unser FAQ')->style(ButtonStyle::Link)->url(route('faq')),
                    Button::make()->label('Unser Team')->style(ButtonStyle::Link)->url(route('team')),
                ),
                ActionRow::make(
                    Button::make()->label('Clip einreichen')->style(ButtonStyle::Link)->url(route('submitclip.create')),
                    Button::make()->label('Clips bewerten')->style(ButtonStyle::Link)->url(route('vote')),
                    Button::make()->label('Zum Dashboard')->style(ButtonStyle::Link)->url(Filament::getPanel('dashboard')->getUrl()),
                ),
            )->accentColor(Color::fromHex('#e71d73')),
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
})->name('embed.discord.default');

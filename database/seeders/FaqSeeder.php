<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq\FaqEntry;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (FaqEntry::count() > 0) {
            return;
        }

        FaqEntry::create([
            'title' => [
                'de' => 'Was ist VHeart?',
            ],
            'body' => [
                'de' => '**VHeart** ist ein Projekt von den Streamern **meynhero, YuraYami, DasOnkeelchen und SilentPandaVT** organisiert wird. VHeart vereint beste Unterhaltung mit einem guten Zweck. Wir präsentieren regelmäßig neue Compilations verschiedenster Streamer. Jedes Video dient einer wichtigen Mission: **Dem Tierschutz.**',
            ],
            'published_at' => now(),
            'order' => 0,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Woher kam die Idee, einen Clip Kanal zu erstellen, der für den guten Zwecke Spenden sammelt?',
            ],
            'body' => [
                'de' => "Die Idee entstand aus dem Wunsch, **gemeinsam ein eigenes Clip-Compilation-Projekt** umzusetzen.\n\nAlleine ist so ein Projekt jedoch kaum zu stemmen, deshalb haben wir uns zusammengeschlossen und VHeart gegründet. Die Idee, daraus zusätzlich eine Spendenaktion zu machen, entstand durch **Meyn**, der Projekte grundsätzlich gerne mit einem guten Zweck verbindet. Durch **SilentPandaVT's** Kontakt zum [Erlebnishof Gerhardsbrunn](https://erlebnishof-gerhardsbrunn.de) lag es nahe, Unterhaltung und Tierschutz miteinander zu kombinieren. So wurde aus einer einfachen Idee ein **Charity-Projekt.**\n\nWir sammeln Clips, unterhalten die Zuschauer und helfen gleichzeitig Tieren.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Wie unterscheidet sich VHeart von anderen Kanälen?',
            ],
            'body' => [
                'de' => "Bei uns steht nicht nur Unterhaltung im Vordergrund, sondern auch ein **fester Charity-Gedanke**, das unterscheidet uns deutlich von klassischen Clip-Compilation-Kanälen.\n\nAußerdem werden die Clips für unsere Videos **aktiv bearbeitet** und nicht einfach nur zusammengestellt. Gleichzeitig bieten wir Cuttern verschiedenster Erfahrungsstufen ein Zuhause, in dem sie lernen, mitwirken und sich weiterentwickeln können.\n\nDadurch ist VHeart nicht einfach nur ein Kanal zum Clips anschauen, sondern **ein gemeinschaftliches Projekt**, an dem viele Menschen aktiv beteiligt sind und mitwirken können.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Was ist die Jury?',
            ],
            'body' => [
                'de' => "Die Jury besteht aus **ausgewählten Personen**, die eingesendete Clips zusätzlich bewerten können. Ihr Voting zählt dabei stärker als das der Community und hilft bei der finalen Auswahl für die Compilations. Um Teil der Jury zu werden, muss man sich bewerben. Die Mitglieder werden anschließend vom Team ausgewählt.\n\nAktuell gibt es **noch keine aktive Jury**, erst mit der Fertigstellung der Website werden die Bewerbungen geöffnet.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Wird es eine Webseite geben?',
            ],
            'body' => [
                'de' => "Ja, eine Website ist **geplant**.\n\nDa wir den Druck für die Entwickler in unserem Team gering halten möchten, haben wir vorübergehend das **Einsenden der Clips über unseren [Discord-Server](https://discord.gg/ThVZHqvXnD)** eingeführt. So bekommt ihr bereits regelmäßig Content, während wir parallel an der Website arbeiten.\n\nUnser Ziel ist es, euch später die bestmögliche Erfahrung über die Website zu bieten, zum Clips einzusenden und zu bewerten.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Derzeitiger Ablauf zum Einreichen der Clips',
            ],
            'body' => [
                'de' => "Jede Woche könnt ihr von **Freitag 12:00 Uhr bis Sonntag 20:00 Uhr** eure Lieblingsclips im dafür vorgesehenen Channel **auf unserem [Discord-Server](https://discord.gg/ThVZHqvXnD) einreichen**, egal ob eigene Clips oder von euren Lieblings-Streamer.\n\nNach Ablauf der Einsendefrist werden die Clips zuerst vom Moderationsteam gesichtet und in eine engere Auswahl gebracht. Diese Auswahl geht anschließend an die Cutter weiter. Die Cutter schauen sich die Clips erneut an und wählen daraus die aus, die sie am besten umsetzen und bearbeiten können. Aus diesen bearbeiteten Clips entsteht am Ende die fertige Compilation.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Wer entscheidet welche Clips in die Complication rein kommen?',
            ],
            'body' => [
                'de' => "Zuerst sichtet das Moderationsteam alle eingesendeten Clips und erstellt eine **engere Auswahl**. Anschließend wählen die Cutter daraus die Clips aus, die sie bearbeiten möchten und die am besten in die Compilation passen.\n\nDie Admins greifen dabei **nicht** in die Auswahl ein.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Kann ich, als Zuschauer, einreichen?',
            ],
            'body' => [
                'de' => "Ja! Auch als Zuschauer kannst du Clips einreichen.\n\nAktuell ist das jedoch nur über unseren **[Discord-Server](https://discord.gg/ThVZHqvXnD)** möglich, da dort die Einsendungen gesammelt und sortiert werden.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Warum öffnet sich der Channel und schließt sich wieder?',
            ],
            'body' => [
                'de' => "Der Einsende-Channel ist bewusst nur zeitweise geöffnet, damit wir die große Menge an Clips sauber und fair sichten können.\n\nDa wir die Sichtung komplett **händisch** durchführen, wäre es sehr unübersichtlich, den Channel **dauerhaft offen** zu lassen. Mit der Zeit würden sich zu viele Clips ansammeln und wir könnten nicht mehr garantieren, dass jede Einsendung berücksichtigt wird.\n\nDurch die festen Zeitfenster können wir:\n- jede Einsendung wirklich anschauen\n- die Arbeitslast für Moderation und Cutter planbar halten\n- regelmäßig neue Compilations veröffentlichen\n- lange Wartezeiten vermeiden\nSo stellen wir sicher, dass Qualität und Fairness für alle erhalten bleiben.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Werden alle eingesendeten Clips in den Videos verwertet?',
            ],
            'body' => [
                'de' => "Nein, nicht jeder eingesendete Clip schafft es automatisch in die Compilation.\n\nAufgrund der großen Menge an Einsendungen hat zwar jeder Clip eine Chance, es können jedoch leider nicht alle berücksichtigt werden.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Kann die Community auch Clips bewerten?',
            ],
            'body' => [
                'de' => "Aktuell gibt es **noch keine** Möglichkeit für die Community, Clips zu bewerten.\n\nGeplant ist jedoch, diese **Funktion später über die Website** bereitzustellen, damit ihr aktiv am Auswahlprozess teilnehmen könnt.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Kann ich mich dem Team Anschließen?',
            ],
            'body' => [
                'de' => "Ja, über den **[Discord-Server](https://discord.gg/ThVZHqvXnD)** im Support Channel kannst du auch sehen welche Bewerbungsphasen gerade offen sind. Bewerben kannst du dich als:\n- Cutter\n- Moderator\n- Später als Jury\n",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Ist es schlimm, wenn ich meinen Clip bei einer anderen Compilation zusätzlich einreiche?',
            ],
            'body' => [
                'de' => "Nein, überhaupt nicht.\n\nDu kannst deine Clips gerne auch bei anderen Compilation-Kanälen einreichen. Jede Compilation hat ihren eigenen Stil und ihre eigene Auswahl, wodurch für Zuschauer eine größere Vielfalt entsteht.\n\nWir unterstützen andere Projekte ausdrücklich, **am Ende profitiert die ganze Community davon.**",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Werden bestimmte Streamer bevorzugt?',
            ],
            'body' => [
                'de' => "Nein, wir achten bewusst auf eine **ausgewogene Mischung** aus großen und kleinen Streamern.\n\nUnser Ziel ist es, möglichst viele verschiedene Creator in den Compilations zu zeigen und für Abwechslung zu sorgen, sowie kleineren eine Chance zu geben, gesehen zu werden.\n\nDa wir teilweise **mehrere hundert Einsendungen erhalten**, können am Ende nur wenige Clips ins Video kommen. Deshalb lohnt es sich immer, weiter Clips einzusenden, **jeder hat eine Chance.**",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Wieso wurde mein Clip nicht genommen?',
            ],
            'body' => [
                'de' => "Es kann viele Gründe geben, warum ein Clip nicht in der Compilation landet, **das bedeutet nicht automatisch, dass dein Clip schlecht ist.**\n\nTypische Gründe können zum Beispiel sein:\n- Zu sehr aus dem Kontext gerissen oder nur mit Insiderwissen verständlich\n- Im Clip passiert zu wenig oder es fehlt ein klarer Moment\n- Starke Sexualisierung oder verletzende Inhalte\n- Fokus auf Tierverletzung oder problematische Situationen\n- Copyright-Inhalte\n- Einsatz von AI-Bildern oder -Modellen\n- Der Creator würde dadurch in ein schlechtes Licht gerückt werden\n- Fehlendes Einverständnis des Streamers\n- Der Clip bietet für die Cutter keine sinnvolle Bearbeitungsmöglichkeit\nDa wir sehr viele Einsendungen erhalten (teilweise über 700 Clips), können wir nur einen kleinen Teil verwenden, dein Clip kann also trotzdem gut gewesen sein.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Holt ihr euch das Einverständnis der Streamer?',
            ],
            'body' => [
                'de' => "Ja, wir holen uns die Bestätigung bei **jedem einzelnen Streamer.**\n\nDer Ablauf ist ganz einfach, wenn du als Streamer selbst deinen Clip einsendest, gilt das automatisch als Zustimmung, dass wir diesen Clip verwenden dürfen.\n\nWird ein Clip von Zuschauern eingesendet und kommt in die **engere Auswahl**, kontaktieren wir den jeweiligen Creator vorher und holen eine Erlaubnis ein.\n\nDer Creator kann dabei entscheiden:\n- Seine Clips generell verwendet werden dürfen\n- Nur dieser eine Clip freigegeben wird\n- Er den Clip vorher immer prüfen möchte\n- Die Nutzung komplett abgelehnt wird\nEine erteilte Zustimmung kann jederzeit für zukünftige Inhalte widerrufen werden. Auf Wunsch können auch bereits veröffentlichte Stellen entfernt werden, bitte gebt dafür die Folge sowie den genauen Timestamp an.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Für wen oder was werden die Spenden gesammelt?',
            ],
            'body' => [
                'de' => "Wir sammeln Spenden für dem **[Erlebnishof Gerhardsbrunn](https://erlebnishof-gerhardsbrunn.de)**.\n\nDer Hof wurde im Jahr 2020 von Janne Bach und Tierarzt Ingmar Meth gegründet. Er dient als Zufluchtsort für Tiere, die aus schlechten Haltungen, Vernachlässigung oder anderen schwierigen Umständen kommen und ein dauerhaftes Zuhause brauchen. Neben der Versorgung und Pflege der Tiere ist der Hof auch ein Begegnungsort für Menschen. Besucher können dort zur Ruhe kommen, den respektvollen Umgang mit Tieren erleben und eine Verbindung zu ihnen aufbauen.\n\nMit den Spenden werden unter anderem **Futter, medizinische Versorgung, Unterhalt der Anlagen sowie die tägliche Betreuung der Tiere finanziert**. Jede Unterstützung hilft direkt dabei, den Tieren langfristig ein sicheres Leben zu ermöglichen.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Wird der YouTube Kanal monetarisiert?',
            ],
            'body' => [
                'de' => 'Ja. Die Einnahmen des **[YouTube-Kanals](https://www.youtube.com/@vheartclips)** werden vollständig gespendet und gehen zu 100 % an den **[Erlebnishof Gerhardsbrunn](https://erlebnishof-gerhardsbrunn.de).**',
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Wie wird sichergestellt, dass die Spenden wirklich an den Erlebnishof gesendet werden?',
            ],
            'body' => [
                'de' => "Die Spendenabwicklung erfolgt über die Plattform **[Betterplace](https://go.vheart.net/spenden)** und wurde in Absprache mit dem **[Erlebnishof Gerhardsbrunn](https://erlebnishof-gerhardsbrunn.de)** eingerichtet.\n\nDadurch wird sichergestellt, dass die Gelder transparent und direkt an den Hof weitergeleitet werden, **ohne dass wir vom Team das Geld anfassen können.**",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Kann ich ein Event machen, wo ich direkt für VHeart spenden sammel?',
            ],
            'body' => [
                'de' => "Grundsätzlich ja, wir freuen uns sehr über jede Unterstützung!\n\nDamit alles korrekt zugeordnet werden kann, bitten wir euch jedoch, solche Aktionen **vorher kurz mit uns abzusprechen und unseren [offiziellen Spendenlink](https://go.vheart.net/spenden) zu verwenden.** So stellen wir sicher, dass das Geld auch wirklich beim Projekt und den Tieren ankommt.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Wie steht ihr Vheart zum Thema AI?',
            ],
            'body' => [
                'de' => "Wir stehen AI-generierten Inhalten im kreativen Bereich eher kritisch gegenüber.\n\nUns ist wichtig, die Arbeit echter Creator und Künstler zu unterstützen. Da bei vielen AI-Inhalten unklar ist, auf welchen Daten sie basieren und wie mit Urheberrecht, Identität und Datenschutz umgegangen wird, verzichten wir in unseren Compilations bewusst auf solche Inhalte.\n\nAuch der Umweltaspekt spielt für uns eine Rolle, da die Erstellung vieler AI-Inhalte einen hohen Energieverbrauch verursachen kann. Deshalb möchten wir, besonders bei eingebundenen Grafiken wie Twitch-Panels oder ähnlichen Elementen, möglichst auf AI-generierte Inhalte verzichten und schließen das auch aus unseren Compilations aus.\n\nUnser Fokus liegt auf echter, von Menschen geschaffener Kreativität aus der Community",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Kann ich VHeart auf meinen Twitch Kanal bewerben?',
            ],
            'body' => [
                'de' => "Natürlich! Du darfst VHeart gerne auf deinem Twitch-Kanal bewerben, zum Beispiel in deinen Panels, als Hinweis im Chat oder direkt im Stream.\n\nWir freuen uns über **jede Unterstützung aus der Community.**",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);

        FaqEntry::create([
            'title' => [
                'de' => 'Wann darf ich reagieren?',
            ],
            'body' => [
                'de' => "Du darfst **jederzeit** auf unsere Videos reagieren, es gibt keine zeitlichen Einschränkungen.\n\nWir würden uns lediglich freuen, wenn dabei der Video-Link im Stream sichtbar ist oder der **[Spendenlink](https://go.vheart.net/spenden)** hervorgehoben wird, damit Zuschauer direkt zum Projekt finden können.",
            ],
            'published_at' => now(),
            'order' => FaqEntry::count() + 1,
        ]);
    }
}

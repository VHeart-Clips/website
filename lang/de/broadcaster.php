<?php

declare(strict_types=1);

return [
    'ban' => [
        'heading' => [
            'temporary' => ':name ist vorübergehend gesperrt',
            'permanent' => ':name ist permanent gesperrt',
        ],
        'description' => 'Clip-Einreichungen sind für diesen Kanal deaktiviert.',
        'temporary' => 'Sperre endet am :date.',
        'permanent' => 'Diese Sperre hat kein Ablaufdatum.',
        'any-questions' => 'Fragen?',
        'discord' => 'Erstelle ein Ticket in unserem Discord.',
    ],
    'enums' => [
        'broadcaster-consent' => [
            'compilations' => 'Compilations',
            'compilations_description' => 'Erlaubt es uns, deine inhalte in unseren Compilations zu verwenden.',
            'shorts' => 'Shorts',
            'shorts_description' => 'Erlaubt es uns, deine inhalte in unseren Shorts zu verwenden.',
        ],
        'dashboard-navigation-group' => [
            'settings' => 'Einstellungen',
        ],
        'dashboard-navigation-item' => [
            'general-settings' => 'Allgemeine Einstellungen',
            'manage-user-filter' => 'Benutzer Regeln',
            'manage-category-filter' => 'Kategorie Regeln',
            'manage-team-member' => 'Team Mitglieder Verwalten',
            'removal-requests' => 'Entfernungs-Anfragen',
        ],
        'broadcaster-permission' => [
            'clips' => 'Clips',
            'submissions-setting' => 'Einsende Einstellungen',
            'category-filter' => 'Kategorie Regeln',
            'user-filter' => 'Benutzer Regeln',
            'removal-requests' => 'Entfernungs-Anfragen',
        ],
        'broadcaster-permission-description' => [
            'clips' => 'Verwalten deiner Clips',
            'submissions-setting' => 'Verwalten deiner Einsende-Einstellungen',
            'category-filter' => 'Verwalten deiner Kategorie-Regeln für das einsenden',
            'user-filter' => 'Verwalten deiner Benutzer-Regeln für das einsenden',
            'removal-requests' => 'Verwalten deiner Entfernungs-Anfragen',
        ],
        'removal-request-status' => [
            'pending' => 'In Bearbeitung',
            'approved' => 'Bestätigt',
            'rejected' => 'Zurückgewiesen',
        ],
    ],
];

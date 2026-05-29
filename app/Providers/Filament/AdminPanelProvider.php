<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('KFC Rotación y Retención')
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString('
                    <link rel="preconnect" href="https://fonts.googleapis.com">
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
                    <style>
                        /* 1. Global Typography */
                        body, html, .fi-body, .fi-sidebar, .fi-topbar, .fi-main {
                            font-family: \'Outfit\', sans-serif !important;
                        }

                        /* 2. KFC Iconic Red-and-White Stripes Accent */
                        .fi-topbar, .fi-sidebar {
                            border-top: 6px solid !important;
                            border-image: repeating-linear-gradient(90deg, #dc2626, #dc2626 12px, #ffffff 12px, #ffffff 24px) 6 !important;
                        }

                        /* 3. Premium Branded Left-Border Accent for Cards & Tables */
                        .fi-wi-widget, .fi-ta-ctn, .fi-fo-layout, .fi-section, .fi-card {
                            border-radius: 12px !important;
                            border-left: 5px solid #dc2626 !important;
                            border-top: 1px solid rgba(220, 38, 38, 0.08) !important;
                            border-right: 1px solid rgba(220, 38, 38, 0.08) !important;
                            border-bottom: 1px solid rgba(220, 38, 38, 0.08) !important;
                            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.01) !important;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            background: rgba(255, 255, 255, 0.95) !important;
                        }

                        .fi-wi-widget:hover, .fi-ta-ctn:hover, .fi-section:hover {
                            transform: translateY(-3px) !important;
                            box-shadow: 0 20px 35px -8px rgba(220, 38, 38, 0.12), 0 8px 12px -6px rgba(220, 38, 38, 0.06) !important;
                            border-color: rgba(220, 38, 38, 0.35) !important;
                        }

                        /* Dark Mode adjustments for Cards */
                        .dark .fi-wi-widget, .dark .fi-ta-ctn, .dark .fi-fo-layout, .dark .fi-section {
                            background: rgba(17, 24, 39, 0.8) !important;
                            border-color: rgba(220, 38, 38, 0.15) !important;
                            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5) !important;
                        }

                        /* 4. Active Navigation Item Crimson Theme */
                        .fi-sidebar-item-button-active {
                            background-color: #dc2626 !important;
                            color: white !important;
                            font-weight: 700 !important;
                            border-radius: 8px !important;
                            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
                        }
                        .fi-sidebar-item-button-active svg {
                            color: white !important;
                        }

                        /* 5. Login Page Background with Generated Crispy Chicken Image & Dark Tint */
                        .fi-simple-layout {
                            background: linear-gradient(180deg, rgba(15, 23, 42, 0.75) 0%, rgba(15, 23, 42, 0.85) 100%), url("/images/kfc_fried_chicken.png") no-repeat center center fixed !important;
                            background-size: cover !important;
                        }

                        .fi-simple-main-ctn {
                            border-radius: 24px !important;
                            border: 1px solid rgba(220, 38, 38, 0.3) !important;
                            box-shadow: 0 30px 70px -10px rgba(0, 0, 0, 0.8) !important;
                            background-color: rgba(15, 23, 42, 0.6) !important;
                            backdrop-filter: blur(20px) !important;
                            -webkit-backdrop-filter: blur(20px) !important;
                            color: white !important;
                        }

                        .fi-simple-main-ctn label, .fi-simple-main-ctn a {
                            color: rgba(255, 255, 255, 0.85) !important;
                        }
                        .fi-simple-main-ctn a:hover {
                            color: #ef4444 !important;
                        }

                        .fi-simple-main-ctn input:focus, .fi-input-wrp:focus-within {
                            border-color: #ef4444 !important;
                            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3) !important;
                        }
                    </style>
                ')
            )
            ->colors([
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Remove default widgets to show our custom analytics dashboard widgets
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

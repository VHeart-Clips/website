<?php

declare(strict_types=1);

namespace App\Providers\Support;

use App\Enums\Filament\LucideIcon;
use Filament\Actions\View\ActionsIconAlias;
use Filament\Forms\View\FormsIconAlias;
use Filament\Infolists\View\InfolistsIconAlias;
use Filament\Notifications\View\NotificationsIconAlias;
use Filament\QueryBuilder\View\QueryBuilderIconAlias;
use Filament\Schemas\View\SchemaIconAlias;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\View\SupportIconAlias;
use Filament\Tables\View\TablesIconAlias;
use Filament\View\PanelsIconAlias;
use Filament\Widgets\View\WidgetsIconAlias;
use Illuminate\Support\ServiceProvider;

class FilamentIconServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        $this->configureActionsIcons();
        $this->configureFormsIcons();
        $this->configureInfolistIcons();
        $this->configureNotificationsIcons();
        $this->configurePanelsIcons();
        $this->configureSchemaIcons();
        $this->configureTablesIcons();
        $this->configureSupportIcons();
        $this->configureWidgetsIcons();
        $this->configureQueryBuilderIcons();
    }

    private function configureActionsIcons(): void
    {
        FilamentIcon::register([
            ActionsIconAlias::ACTION_GROUP => LucideIcon::EllipsisVertical,
            ActionsIconAlias::CREATE_ACTION_GROUPED => LucideIcon::Plus,
            ActionsIconAlias::DELETE_ACTION => LucideIcon::Trash,
            ActionsIconAlias::DELETE_ACTION_GROUPED => LucideIcon::Trash,
            ActionsIconAlias::DELETE_ACTION_MODAL => LucideIcon::Trash,
            ActionsIconAlias::DETACH_ACTION => LucideIcon::X,
            ActionsIconAlias::DETACH_ACTION_MODAL => LucideIcon::X,
            ActionsIconAlias::DISSOCIATE_ACTION => LucideIcon::X,
            ActionsIconAlias::DISSOCIATE_ACTION_MODAL => LucideIcon::X,
            ActionsIconAlias::EDIT_ACTION => LucideIcon::SquarePen,
            ActionsIconAlias::EDIT_ACTION_GROUPED => LucideIcon::SquarePen,
            ActionsIconAlias::EXPORT_ACTION_GROUPED => LucideIcon::Download,
            ActionsIconAlias::FORCE_DELETE_ACTION => LucideIcon::Trash,
            ActionsIconAlias::FORCE_DELETE_ACTION_GROUPED => LucideIcon::Trash,
            ActionsIconAlias::FORCE_DELETE_ACTION_MODAL => LucideIcon::Trash,
            ActionsIconAlias::IMPORT_ACTION_GROUPED => LucideIcon::Upload,
            ActionsIconAlias::MODAL_CONFIRMATION => LucideIcon::TriangleAlert,
            ActionsIconAlias::REPLICATE_ACTION => LucideIcon::Copy,
            ActionsIconAlias::REPLICATE_ACTION_GROUPED => LucideIcon::Copy,
            ActionsIconAlias::RESTORE_ACTION => LucideIcon::Undo2,
            ActionsIconAlias::RESTORE_ACTION_GROUPED => LucideIcon::Undo2,
            ActionsIconAlias::RESTORE_ACTION_MODAL => LucideIcon::Undo2,
            ActionsIconAlias::VIEW_ACTION => LucideIcon::Eye,
            ActionsIconAlias::VIEW_ACTION_GROUPED => LucideIcon::Eye,
        ]);
    }

    private function configureFormsIcons(): void
    {
        FilamentIcon::register([
            FormsIconAlias::COMPONENTS_BUILDER_ACTIONS_CLONE => LucideIcon::Copy,
            FormsIconAlias::COMPONENTS_BUILDER_ACTIONS_COLLAPSE => LucideIcon::ChevronUp,
            FormsIconAlias::COMPONENTS_BUILDER_ACTIONS_DELETE => LucideIcon::Trash,
            FormsIconAlias::COMPONENTS_BUILDER_ACTIONS_EXPAND => LucideIcon::ChevronDown,
            FormsIconAlias::COMPONENTS_BUILDER_ACTIONS_MOVE_DOWN => LucideIcon::ArrowDown,
            FormsIconAlias::COMPONENTS_BUILDER_ACTIONS_MOVE_UP => LucideIcon::ArrowUp,
            FormsIconAlias::COMPONENTS_BUILDER_ACTIONS_REORDER => LucideIcon::ArrowUpDown,
            FormsIconAlias::COMPONENTS_CHECKBOX_LIST_SEARCH_FIELD => LucideIcon::Search,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_DRAG_CROP => LucideIcon::Crop,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_DRAG_MOVE => LucideIcon::Move,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_FLIP_HORIZONTAL => LucideIcon::FlipHorizontal2,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_FLIP_VERTICAL => LucideIcon::FlipVertical2,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_MOVE_DOWN => LucideIcon::CircleArrowDown,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_MOVE_LEFT => LucideIcon::CircleArrowLeft,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_MOVE_RIGHT => LucideIcon::CircleArrowRight,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_MOVE_UP => LucideIcon::CircleArrowUp,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_ROTATE_LEFT => LucideIcon::Undo2,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_ROTATE_RIGHT => LucideIcon::Redo2,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_ZOOM_100 => LucideIcon::SearchSlash,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_ZOOM_IN => LucideIcon::ZoomIn,
            FormsIconAlias::COMPONENTS_FILE_UPLOAD_EDITOR_ACTIONS_ZOOM_OUT => LucideIcon::ZoomOut,
            FormsIconAlias::COMPONENTS_KEY_VALUE_ACTIONS_DELETE => LucideIcon::Trash,
            FormsIconAlias::COMPONENTS_KEY_VALUE_ACTIONS_REORDER => LucideIcon::ArrowUpDown,
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_CLONE => LucideIcon::Copy,
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_COLLAPSE => LucideIcon::ChevronUp,
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_DELETE => LucideIcon::Trash,
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_EXPAND => LucideIcon::ChevronDown,
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_MOVE_DOWN => LucideIcon::ArrowDown,
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_MOVE_UP => LucideIcon::ArrowUp,
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_REORDER => LucideIcon::ArrowUpDown,
            FormsIconAlias::COMPONENTS_RICH_EDITOR_PANELS_CUSTOM_BLOCKS_CLOSE_BUTTON => LucideIcon::X,
            FormsIconAlias::COMPONENTS_RICH_EDITOR_PANELS_CUSTOM_BLOCK_DELETE_BUTTON => LucideIcon::Trash,
            FormsIconAlias::COMPONENTS_RICH_EDITOR_PANELS_CUSTOM_BLOCK_EDIT_BUTTON => LucideIcon::SquarePen,
            FormsIconAlias::COMPONENTS_RICH_EDITOR_PANELS_MERGE_TAGS_CLOSE_BUTTON => LucideIcon::X,
            FormsIconAlias::COMPONENTS_SELECT_ACTIONS_CREATE_OPTION => LucideIcon::Plus,
            FormsIconAlias::COMPONENTS_SELECT_ACTIONS_EDIT_OPTION => LucideIcon::SquarePen,
            FormsIconAlias::COMPONENTS_TEXT_INPUT_ACTIONS_COPY => LucideIcon::ClipboardCopy,
            FormsIconAlias::COMPONENTS_TEXT_INPUT_ACTIONS_HIDE_PASSWORD => LucideIcon::EyeClosed,
            FormsIconAlias::COMPONENTS_TEXT_INPUT_ACTIONS_SHOW_PASSWORD => LucideIcon::Eye,
            FormsIconAlias::COMPONENTS_TOGGLE_BUTTONS_BOOLEAN_FALSE => LucideIcon::X,
            FormsIconAlias::COMPONENTS_TOGGLE_BUTTONS_BOOLEAN_TRUE => LucideIcon::Check,
        ]);
    }

    private function configureInfolistIcons(): void
    {
        FilamentIcon::register([
            InfolistsIconAlias::COMPONENTS_ICON_ENTRY_FALSE => LucideIcon::X,
            InfolistsIconAlias::COMPONENTS_ICON_ENTRY_TRUE => LucideIcon::Check,
        ]);
    }

    private function configureNotificationsIcons(): void
    {
        FilamentIcon::register([
            NotificationsIconAlias::DATABASE_MODAL_EMPTY_STATE => LucideIcon::BellOff,
            NotificationsIconAlias::NOTIFICATION_CLOSE_BUTTON => LucideIcon::X,
            NotificationsIconAlias::NOTIFICATION_DANGER => LucideIcon::CircleX,
            NotificationsIconAlias::NOTIFICATION_INFO => LucideIcon::Info,
            NotificationsIconAlias::NOTIFICATION_SUCCESS => LucideIcon::CircleCheck,
            NotificationsIconAlias::NOTIFICATION_WARNING => LucideIcon::CircleAlert,
        ]);
    }

    private function configurePanelsIcons(): void
    {
        FilamentIcon::register([
            PanelsIconAlias::GLOBAL_SEARCH_FIELD => LucideIcon::Search,
            PanelsIconAlias::PAGES_DASHBOARD_ACTIONS_FILTER => LucideIcon::Funnel,
            PanelsIconAlias::PAGES_DASHBOARD_NAVIGATION_ITEM => LucideIcon::Home,
            PanelsIconAlias::PAGES_PASSWORD_RESET_REQUEST_PASSWORD_RESET_ACTIONS_LOGIN => LucideIcon::ArrowLeft,
            PanelsIconAlias::PAGES_PASSWORD_RESET_REQUEST_PASSWORD_RESET_ACTIONS_LOGIN_RTL => LucideIcon::ArrowRight,
            PanelsIconAlias::RESOURCES_PAGES_EDIT_RECORD_NAVIGATION_ITEM => LucideIcon::SquarePen,
            PanelsIconAlias::RESOURCES_PAGES_MANAGE_RELATED_RECORDS_NAVIGATION_ITEM => LucideIcon::Layers,
            PanelsIconAlias::RESOURCES_PAGES_VIEW_RECORD_NAVIGATION_ITEM => LucideIcon::Layers,
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => LucideIcon::ChevronLeft,
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL => LucideIcon::ChevronRight,
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => LucideIcon::ChevronRight,
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL => LucideIcon::ChevronLeft,
            PanelsIconAlias::SIDEBAR_GROUP_COLLAPSE_BUTTON => LucideIcon::ChevronUp,
            PanelsIconAlias::SUB_NAVIGATION_MOBILE_MENU_BUTTON => LucideIcon::ChevronDown,
            PanelsIconAlias::TENANT_MENU_BILLING_BUTTON => LucideIcon::CreditCard,
            PanelsIconAlias::TENANT_MENU_PROFILE_BUTTON => LucideIcon::Settings,
            PanelsIconAlias::TENANT_MENU_REGISTRATION_BUTTON => LucideIcon::Plus,
            PanelsIconAlias::TENANT_MENU_TOGGLE_BUTTON => LucideIcon::ChevronDown,
            PanelsIconAlias::THEME_SWITCHER_LIGHT_BUTTON => LucideIcon::Sun,
            PanelsIconAlias::THEME_SWITCHER_DARK_BUTTON => LucideIcon::Moon,
            PanelsIconAlias::THEME_SWITCHER_SYSTEM_BUTTON => LucideIcon::Monitor,
            PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON => LucideIcon::X,
            PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON => LucideIcon::Menu,
            PanelsIconAlias::TOPBAR_GROUP_TOGGLE_BUTTON => LucideIcon::ChevronDown,
            PanelsIconAlias::TOPBAR_OPEN_DATABASE_NOTIFICATIONS_BUTTON => LucideIcon::Bell,
            PanelsIconAlias::SIDEBAR_OPEN_DATABASE_NOTIFICATIONS_BUTTON => LucideIcon::Bell,
            PanelsIconAlias::USER_MENU_PROFILE_ITEM => LucideIcon::CircleUser,
            PanelsIconAlias::USER_MENU_LOGOUT_BUTTON => LucideIcon::LogOut,
            PanelsIconAlias::USER_MENU_TOGGLE_BUTTON => LucideIcon::ChevronUp,
            PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON => LucideIcon::LogOut,
            PanelsIconAlias::WIDGETS_FILAMENT_INFO_OPEN_DOCUMENTATION_BUTTON => LucideIcon::BookOpen,
            PanelsIconAlias::WIDGETS_FILAMENT_INFO_OPEN_GITHUB_BUTTON => LucideIcon::Code,
        ]);
    }

    private function configureSchemaIcons(): void
    {
        FilamentIcon::register([
            SchemaIconAlias::COMPONENTS_CALLOUT_DANGER => LucideIcon::CircleX,
            SchemaIconAlias::COMPONENTS_CALLOUT_INFO => LucideIcon::Info,
            SchemaIconAlias::COMPONENTS_CALLOUT_SUCCESS => LucideIcon::CheckCircle,
            SchemaIconAlias::COMPONENTS_CALLOUT_WARNING => LucideIcon::CircleAlert,
            SchemaIconAlias::COMPONENTS_TABS_DROPDOWN_TRIGGER_BUTTON => LucideIcon::ChevronDown,
            SchemaIconAlias::COMPONENTS_TABS_MORE_TABS_BUTTON => LucideIcon::Ellipsis,
            SchemaIconAlias::COMPONENTS_WIZARD_COMPLETED_STEP => LucideIcon::Check,
        ]);
    }

    private function configureTablesIcons(): void
    {
        FilamentIcon::register([
            TablesIconAlias::ACTIONS_DISABLE_REORDERING => LucideIcon::Check,
            TablesIconAlias::ACTIONS_ENABLE_REORDERING => LucideIcon::ArrowUpDown,
            TablesIconAlias::ACTIONS_FILTER => LucideIcon::Funnel,
            TablesIconAlias::ACTIONS_GROUP => LucideIcon::Layers,
            TablesIconAlias::ACTIONS_OPEN_BULK_ACTIONS => LucideIcon::EllipsisVertical,
            TablesIconAlias::ACTIONS_COLUMN_MANAGER => LucideIcon::Columns3,
            TablesIconAlias::COLUMNS_COLLAPSE_BUTTON => LucideIcon::ChevronDown,
            TablesIconAlias::COLUMNS_ICON_COLUMN_FALSE => LucideIcon::XCircle,
            TablesIconAlias::COLUMNS_ICON_COLUMN_TRUE => LucideIcon::CheckCircle,
            TablesIconAlias::EMPTY_STATE => LucideIcon::X,
            TablesIconAlias::FILTERS_QUERY_BUILDER_CONSTRAINTS_BOOLEAN => LucideIcon::CheckCircle,
            TablesIconAlias::FILTERS_QUERY_BUILDER_CONSTRAINTS_DATE => LucideIcon::Calendar,
            TablesIconAlias::FILTERS_QUERY_BUILDER_CONSTRAINTS_NUMBER => LucideIcon::Variable,
            TablesIconAlias::FILTERS_QUERY_BUILDER_CONSTRAINTS_RELATIONSHIP => LucideIcon::Expand,
            TablesIconAlias::FILTERS_QUERY_BUILDER_CONSTRAINTS_SELECT => LucideIcon::ChevronsUpDown,
            TablesIconAlias::FILTERS_QUERY_BUILDER_CONSTRAINTS_TEXT => LucideIcon::Languages,
            TablesIconAlias::FILTERS_REMOVE_ALL_BUTTON => LucideIcon::X,
            TablesIconAlias::GROUPING_COLLAPSE_BUTTON => LucideIcon::ChevronUp,
            TablesIconAlias::HEADER_CELL_SORT_ASC_BUTTON => LucideIcon::ArrowDownAZ,
            TablesIconAlias::HEADER_CELL_SORT_BUTTON => LucideIcon::ArrowDownUp,
            TablesIconAlias::HEADER_CELL_SORT_DESC_BUTTON => LucideIcon::ArrowUpAZ,
            TablesIconAlias::REORDER_HANDLE => LucideIcon::Menu,
            TablesIconAlias::SEARCH_FIELD => LucideIcon::Search,
        ]);
    }

    private function configureSupportIcons(): void
    {
        FilamentIcon::register([
            SupportIconAlias::BADGE_DELETE_BUTTON => LucideIcon::X,
            SupportIconAlias::BREADCRUMBS_SEPARATOR => LucideIcon::ChevronRight,
            SupportIconAlias::BREADCRUMBS_SEPARATOR_RTL => LucideIcon::ChevronLeft,
            SupportIconAlias::MODAL_CLOSE_BUTTON => LucideIcon::X,
            SupportIconAlias::PAGINATION_FIRST_BUTTON => LucideIcon::ChevronsLeft,
            SupportIconAlias::PAGINATION_FIRST_BUTTON_RTL => LucideIcon::ChevronsRight,
            SupportIconAlias::PAGINATION_LAST_BUTTON => LucideIcon::ChevronsRight,
            SupportIconAlias::PAGINATION_LAST_BUTTON_RTL => LucideIcon::ChevronsLeft,
            SupportIconAlias::PAGINATION_NEXT_BUTTON => LucideIcon::ChevronRight,
            SupportIconAlias::PAGINATION_NEXT_BUTTON_RTL => LucideIcon::ChevronLeft,
            SupportIconAlias::PAGINATION_PREVIOUS_BUTTON => LucideIcon::ChevronLeft,
            SupportIconAlias::PAGINATION_PREVIOUS_BUTTON_RTL => LucideIcon::ChevronRight,
            SupportIconAlias::SECTION_COLLAPSE_BUTTON => LucideIcon::ChevronUp,
        ]);
    }

    private function configureWidgetsIcons(): void
    {
        FilamentIcon::register([
            WidgetsIconAlias::CHART_WIDGET_FILTER => LucideIcon::Funnel,
            WidgetsIconAlias::CHART_WIDGET_EMPTY_STATE => LucideIcon::XCircle,
        ]);
    }

    private function configureQueryBuilderIcons(): void
    {
        FilamentIcon::register([
            QueryBuilderIconAlias::ADD_RULE_ACTION => LucideIcon::Plus,
            QueryBuilderIconAlias::CONSTRAINTS_BOOLEAN => LucideIcon::CheckCircle,
            QueryBuilderIconAlias::CONSTRAINTS_DATE => LucideIcon::Calendar,
            QueryBuilderIconAlias::CONSTRAINTS_NUMBER => LucideIcon::Variable,
            QueryBuilderIconAlias::CONSTRAINTS_RELATIONSHIP => LucideIcon::Expand,
            QueryBuilderIconAlias::CONSTRAINTS_SELECT => LucideIcon::ChevronsUpDown,
            QueryBuilderIconAlias::CONSTRAINTS_TEXT => LucideIcon::Languages,
            QueryBuilderIconAlias::OR_GROUP_BLOCK => LucideIcon::Slash,
            QueryBuilderIconAlias::OR_GROUP_ADD_GROUP_ACTION => LucideIcon::Plus,
        ]);
    }
}

@if ($this->isEditing)
    @include('filament.clusters.configuraciones.resources.empresas.pages.view-empresa-edit')
@else
    @include('filament.clusters.configuraciones.resources.empresas.pages.view-empresa-custom')
@endif

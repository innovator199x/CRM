<style>
    .scrollview {
        overflow-y: scroll;
        height: 200px;

        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    .scrollview::-webkit-scrollbar {
        display: none;
    }

    .region-collapse:not(.collapsed) {
        margin-bottom: 4px;
    }
    .region-collapse .fa-angle-right {
        display: none;
    }
    .region-collapse .fa-angle-down {
        display: inline;
    }
    .region-collapse.collapsed .fa-angle-right {
        display: inline;
    }
    .region-collapse.collapsed .fa-angle-down {
        display: none;
    }

    .fancybox-form-container:not(.date-only) {
        min-width: 420px;
    }

    #fancybox-tech_region-included_regions {
        max-width: 600px;
    }

    #container-assigned_technicians {
        max-width: 400px;
    }
    .assigned_technician {
        display: inline-block;
        padding: 4px;
        border-radius: 8px;
        margin-top: 2px;
        margin-bottom: 2px;
        margin-right: 2px;
        background-color: #5dca73;
        color: white;
        font-size: 12pt;
    }
    .assigned_technician a {
        color: inherit;
        margin-left: 3px;
    }

    .assigned_technician i {
        color: inherit;
        margin-left: 3px;
    }

    .ui-autocomplete {
        z-index: 10000 !important;
    }
</style>
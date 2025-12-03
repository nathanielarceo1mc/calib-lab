import './bootstrap';

import Alpine from 'alpinejs';
import { DataTable } from 'simple-datatables';
import 'simple-datatables/dist/style.css';

window.Alpine = Alpine;
window.DataTable = DataTable;

Alpine.start();

/**
 * Receiving table initializer
 * (called only on receiving.index via @push('scripts'))
 */
window.initReceivingTable = function () {
    const table = document.getElementById('receivingTable');

    if (!table || !window.DataTable) {
        return;
    }

    const dataTable = new DataTable(table, {
        searchable: true,
        fixedHeight: true,
        perPage: 25,
    });

    const searchInput = document.getElementById('receivingSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            dataTable.search(this.value);
        });
    }
};

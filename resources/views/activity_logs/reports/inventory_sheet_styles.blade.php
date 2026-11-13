{{-- The styles of the Medicines with notes and Yearly Medicine Inventory sheets on screen (re-audit 2026-11-13, DRY: both wrote them out);
     included inside their <style>. --}}
.inv-sheet { background: #fff; border: 1px solid #e4e6ef; border-radius: .5rem; padding: 1.5rem; }
.inv-heading { text-align: center; margin-bottom: 1rem; }
.inv-clinic { font-weight: 600; }
.inv-title { font-size: 1.35rem; font-weight: 700; letter-spacing: 1px; }
.inv-period { color: #5e6278; }
.inv-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: .85rem; }
.inv-table th, .inv-table td { border: 1px solid #d9d9e3; padding: .3rem .5rem; }
.inv-table thead th { background: #f1f1f4; text-align: center; }
.inv-table .item { text-align: left; }
.inv-table .num { text-align: center; min-width: 64px; }
.inv-table .zero { color: #b5b5c3; }
.inv-table .inv-notes { font-size: .78rem; color: #7e8299; }
.inv-table .inv-check { color: #f1416c; }
.inv-table .empty { text-align: center; color: #7e8299; padding: 1rem; }
.inv-note { color: #7e8299; font-size: .8rem; }
.acc-sign { width: 100%; margin-top: 1.5rem; }
.acc-sign td { width: 50%; vertical-align: top; padding: 0 1rem; }
.acc-sign-name { font-weight: 700; margin-top: 2rem; border-top: 1px solid #000; display: inline-block; min-width: 240px; padding-top: .25rem; }
@media print { .inv-actions { display: none; } .inv-sheet { border: 0; padding: 0; } }

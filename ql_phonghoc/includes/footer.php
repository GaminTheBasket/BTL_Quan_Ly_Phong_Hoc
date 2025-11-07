</div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>

    <script>
        // Khởi tạo DataTables cho tất cả bảng có class .datatable
        document.addEventListener('DOMContentLoaded', function () {
            const tables = document.querySelectorAll('.datatable');
            tables.forEach(table => {
                new DataTable(table, {
                    language: {
                        // Thêm Tiếng Việt cho DataTables
                        url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/vi.json'
                    }
                });
            });
        });
    </script>
    </body>
</html>
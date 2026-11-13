{{-- Opens Change Password by itself while the account still has the default password ($hasDefaultPassword, from the
     dashboard's controller). Shared by the admin, doctor and staff dashboards (re-audit 2026-11-13, DRY: each had a copy). --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var hasDefaultPassword = @json($hasDefaultPassword ?? false);
        if (hasDefaultPassword) {
            setTimeout(function() {
                var btn = document.getElementById('changePassword');
                if (btn) {
                    btn.click();
                } else if (typeof $ !== 'undefined') {
                    $('#changePassword').click();
                }
            }, 1000);
        }
    });
</script>

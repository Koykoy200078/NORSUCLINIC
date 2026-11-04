listenClick('.role-delete-btn', function (event) {
    let roleRecordId = $(event.currentTarget).attr('data-id')
    deleteItem(route('roles.destroy', roleRecordId),  Lang.get('js.roles'))
})

function updateRoleSelectAll () {
    const all = document.getElementById('checkAllPermission')
    if (!all) {
        return
    }
    const permissions = Array.from(document.querySelectorAll('.role-permission'))
    const checked = permissions.filter(permission => permission.checked).length
    all.checked = permissions.length > 0 && checked === permissions.length
    all.indeterminate = checked > 0 && checked < permissions.length
}

listenChange('#checkAllPermission', function (event) {
    document.querySelectorAll('.role-permission').forEach(permission => {
        permission.checked = event.currentTarget.checked
    })
    updateRoleSelectAll()
})
listenChange('.role-permission', updateRoleSelectAll)

// Set the box right away: the page is a normal full page load and the form is already in the page when this script runs.
// The event covers a script that runs before the form is drawn.
updateRoleSelectAll()
document.addEventListener('DOMContentLoaded', updateRoleSelectAll)

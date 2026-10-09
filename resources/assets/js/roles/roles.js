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

// Set the box right away: the page is a normal full page load (Turbo is not part of this bundle, so "turbo:load" never
// fires) and the form is already in the page when this script runs. The two events cover an early script and a Turbo
// visit if Turbo is ever added.
updateRoleSelectAll()
document.addEventListener('DOMContentLoaded', updateRoleSelectAll)
document.addEventListener('turbo:load', updateRoleSelectAll)

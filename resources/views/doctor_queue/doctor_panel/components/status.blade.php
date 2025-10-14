<div class="d-flex align-items-center">
    <span class="slot-color-dot badge bg-{{getBadgeStatusColor($row->status)}} badge-circle me-2"></span>
    <select class="io-select2 form-select doctor-appointment-status-change appointment-status" style="min-width: 150px; max-width:150px;"
        data-id="{{$row->id}}">
        <option class="booked" disabled value="{{ $book}}" {{$row->status ==
                    $book ? 'selected' : ''}}>{{__('messages.common.'.strtolower(\App\Models\PatientQueue::STATUS[1]))}}
        </option>
        <option value="{{ $accepted}}" {{$row->status ==
                    $accepted ? 'selected' : ''}} {{$row->status == $accepted
            ? 'selected'
            : ''}} {{( $row->status == $cancel || $row->status == $finished)
            ? 'disabled'
            : ''}}>{{__('messages.common.'.strtolower(\App\Models\PatientQueue::STATUS[2]))}}
        </option>
        <option value="{{ $finished}}" {{$row->status ==
                    $finished ? 'selected' : ''}} {{($row->status == $cancel ||
            $row->status == $book) ? 'disabled' : ''}}>{{__('messages.common.'.strtolower(\App\Models\PatientQueue::STATUS[3]))}}
        </option>
        <option value="{{$cancel}}" {{$row->status ==
                    $cancel ? 'selected' : ''}} {{$row->status == $accepted
            ? 'disabled'
            : ''}} {{$row->status == $finished ? 'disabled' : ''}}>{{__('messages.common.'.strtolower(\App\Models\PatientQueue::STATUS[4]))}}
        </option>
    </select>
</div>


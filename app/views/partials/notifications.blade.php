@foreach ($errors as $message)
    <div class="notification notification-error">{{ $message }}</div>
@endforeach

@foreach ($successes as $message)
    <div class="notification notification-success">{{ $message }}</div>
@endforeach

@foreach ($informations as $message)
    <div class="notification notification-information">{{ $message }}</div>
@endforeach
<!DOCTYPE html>
<html lang="en">

<head>
  @php
  $appName = \App\Models\Setting::first()?->app_name ?? 'Customer';
  @endphp

  <title>{{ $title ?? 'Customer' }} - {{ $appName }}</title>


  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="{{asset('frontend/assets/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="{{asset('frontend/assets/css/animate.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/assets/css/owl.carousel.min.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/assets/css/owl.theme.default.min.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/assets/css/magnific-popup.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/assets/css/bootstrap-datepicker.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/assets/css/jquery.timepicker.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/assets/css/flaticon.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/assets/css/style.css')}}">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
  <link rel="stylesheet" href="{{ asset('frontend/assets/css/mastercustomer.css') }}?v={{ time() }}">
</head>



<body>
  <div class="mobile-view {{ auth()->check() && auth()->user()->role === 'courier' ? 'courier-theme' : '' }}">

    @yield('navbar')

    <div class="mobile-content">
      @if (($title ?? '') === 'Home')
      @yield('product')
      @elseif (($title ?? '') === 'Product')
      @yield('product')
      @endif

      @yield('content')
    </div>

    <!-- Bottom Navigation -->
    @yield('navbot')

  </div>

  <!-- Loader -->
  <div id="ftco-loader" class="show fullscreen">
    <svg class="circular" width="48px" height="48px">
      <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
      <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#ff6b35" />
    </svg>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({
      duration: 800,
      once: true
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    function needVerify(e) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Akses Ditolak',
        text: 'Akun Anda harus diverifikasi admin sebelum mengajukan seller.',
        confirmButtonText: 'Mengerti',
        confirmButtonColor: '#ff6b35'
      });
    }
  </script>

  <script src="{{asset('frontend/assets/js/jquery.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/jquery-migrate-3.0.1.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/popper.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/bootstrap.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/jquery.easing.1.3.js')}}"></script>
  <script src="{{asset('frontend/assets/js/jquery.waypoints.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/jquery.stellar.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/jquery.animateNumber.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/bootstrap-datepicker.js')}}"></script>
  <script src="{{asset('frontend/assets/js/jquery.timepicker.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/owl.carousel.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/jquery.magnific-popup.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/scrollax.min.js')}}"></script>
  <script src="{{asset('frontend/assets/js/main.js')}}"></script>

  @if(session('gagal'))
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Akses Ditolak',
      text: "{{ session('gagal') }}",
      confirmButtonColor: '#d33',
      confirmButtonText: 'OK'
    })
  </script>
  @endif

  @if(session('sukses'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: "{{ session('sukses') }}",
      confirmButtonColor: '#ff6b35'
    })
  </script>
  @endif

  @stack('scripts')

</body>

</html>
{{-- resources/views/employee/attendance/form.blade.php --}}

@extends('backend.layouts.app')

@section('title', localize('Add Attendance'))

@push('css')
    <link href="{{ asset('backend/assets/custom.css') }}" rel="stylesheet">
    <style>
        /* Custom styles for scanner */
        #barcodeScanner {
            width: 100%;
            height: 300px;
            border: 1px solid #ccc;
            position: relative;
            margin: 0 auto;
        }
        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 200px;
            height: 200px;
            margin-top: -100px;
            margin-left: -100px;
            border: 2px dashed #00B074;
            border-radius: 10px;
        }
        .barcode-result {
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
        }
    </style>
@endpush

@section('employeeside')
    @include('employee.emp-side')
@endsection

@section('content')
    @include('backend.layouts.common.message')
    <div class="container">
        <h2 class="my-4">{{ localize('add_attendance') }}</h2>

        <form action="{{ route('employee.attendance.add') }}" method="POST" id="attendanceForm">
            @csrf

            <!-- Hidden inputs -->
            <input type="hidden" name="barcode_data" id="barcode_data">
            <input type="hidden" name="latitude" id="latitude" value="{{ $latitude }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ $longitude }}">
            <input type="hidden" name="datetime" id="datetime">

            <!-- Scanner area -->
            <div class="form-group">
                <label for="barcode">{{ localize('Scanning Attendance') }}</label>
                <div id="barcodeScanner">
                    <div class="scanner-overlay"></div>
                </div>
                <div class="barcode-result" id="barcodeResult"></div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <!-- Include QuaggaJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize QuaggaJS
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector('#barcodeScanner'),
                    constraints: {
                        facingMode: "environment" // Use the back camera
                    }
                },
                decoder: {
                    readers: ["code_128_reader", "ean_reader", "ean_8_reader", "upc_reader", "upc_e_reader"] // Add other barcode types if needed
                },
                locate: true // Enable localization for better accuracy
            }, function(err) {
                if (err) {
                    console.error(err);
                    alert("{{ __('Error initializing barcode scanner.') }}");
                    return;
                }
                Quagga.start();
            });

            // On detected barcode
            Quagga.onDetected(function(result) {
                var code = result.codeResult.code;

                // Assuming the barcode contains "latitude,longitude"
                var parts = code.split(',');
                if(parts.length >= 2){
                    var scannedLatitude = parseFloat(parts[0]);
                    var scannedLongitude = parseFloat(parts[1]);

                    // Compare scanned latitude and longitude with expected
                    var expectedLatitude = parseFloat(document.getElementById('latitude').value);
                    var expectedLongitude = parseFloat(document.getElementById('longitude').value);

                    // Allow a small margin of error for floating point comparison
                    var margin = 0.0001;

                    if(
                        Math.abs(scannedLatitude - expectedLatitude) < margin &&
                        Math.abs(scannedLongitude - expectedLongitude) < margin
                    ){
                        // Set the barcode data
                        document.getElementById('barcode_data').value = code;

                        // Set the datetime to current time
                        var now = new Date();
                        var datetime = now.toISOString().slice(0,19).replace('T', ' ');
                        document.getElementById('datetime').value = datetime;

                        // Display the scanned code and datetime
                        document.getElementById('barcodeResult').innerText = "{{ localize('Scanned Code') }}: " + code + " | " + "{{ localize('Time') }}: " + datetime;

                        // Stop the scanner
                        Quagga.stop();

                        // Submit the form after a short delay
                        setTimeout(function() {
                            document.getElementById('attendanceForm').submit();
                        }, 1000); // 1 second delay
                    } else {
                        // Scanned barcode does not match expected location
                        alert("{{ __('Scanned location does not match company location.') }}");
                    }
                } else {
                    // Invalid barcode format
                    alert("{{ __('Invalid barcode format.') }}");
                }
            });
        });
    </script>
@endpush

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hidden Payment Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Kuveyt Turk API Test</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('k-test.pay') }}" method="POST">
                        @csrf
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Amount (TRY)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Card Holder Name</label>
                            <input type="text" name="card_holder_name" class="form-control" placeholder="John Doe" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" name="card_number" class="form-control" placeholder="5188 9619 3919 2544" required>
                            <small class="text-muted">Kuveyt Turk Test Card</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Expiry (MM/YY)</label>
                                    <input type="text" name="expiry" class="form-control" placeholder="MM/YY" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">CVV</label>
                                    <input type="text" name="cvv" class="form-control" placeholder="123" maxlength="4" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Test Pay (3D Secure)</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

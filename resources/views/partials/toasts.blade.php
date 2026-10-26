@if (session('sucesso'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Toast.success(@json(session('sucesso')));
        });
    </script>
@endif

@if (session('erro'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Toast.error(@json(session('erro')));
        });
    </script>
@endif

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @foreach ($errors->all() as $error)
                Toast.error(@json($error));
            @endforeach
        });
    </script>
@endif

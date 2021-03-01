$(document).ready(function() {
    $(document).on('click', 'input.back', function() {
        window.history.back();
    });
    
    $(document).on('click', 'a.leave', function() {
        return confirm('Выйти из аккаунта?');
    });
    
});
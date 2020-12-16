<script src="/desktop/js/jquery.min.js"></script>
<script src="/desktop/js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/desktop/js/ie10-viewport-bug-workaround.js"></script>

<script src="/desktop/js/owlCarousel/owl.carousel.min.js"></script>
<script src="/desktop/js/moment.min.js"></script>
<script src="/desktop/js/bootstrap-material-datetimepicker/bootstrap-material-datetimepicker.js"></script>
<script src="/desktop/js/jquery.inputmask.bundle.js"></script>
<script src="/desktop/js/starrr/starrr.js"></script> 
<script src="/desktop/js/scripts.js"></script>
<script src="/js/loading.js"></script>
@if (Route::current()->getName() == 'user.appointment.select-service')
    <script src="/desktop/js/select-service.js"></script>
@endif
@if (Route::current()->getName() == 'user.appointment.add-ons' )
    <script src="/desktop/js/add-ons.js"></script>
@endif
@if (Route::current()->getName() == 'user.appointment.select-pet' )
    <script src="/desktop/js/select-pet.js"></script>
@endif
@if (Route::current()->getName() == 'user.appointment.date-time' )
    <script src="/desktop/js/date-time.js"></script>
@endif
@if (Route::current()->getName() == 'user.appointment.login-signup' )
    <script src="/desktop/js/login-signup.js"></script>
@endif

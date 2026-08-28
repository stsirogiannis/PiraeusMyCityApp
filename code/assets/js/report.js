document.addEventListener('DOMContentLoaded', function (){ // εκτέλεση αφού φορτώσει η HTML

    //άντληση στοιχείων φόρμας
    var form= document.getElementById('reportForm');
    var title= document.getElementById('title');
    var category= document.getElementById('category_id');
    var address = document.getElementById('address');
    var description= document.getElementById('description');
    var username= document.getElementById('username');
    var video= document.getElementById('video');
    var submitBtn= document.getElementById('submitBtn');
    var typeNamed= document.getElementById('typeNamed');
    var typeAnon= document.getElementById('typeAnon');
    var userWrapper= document.getElementById('usernameWrapper');
    var typeError= document.getElementById('typeError');

    //χρωματισμός πεδίου φόρμας με πράσινο ή κόκκινο (έγκυρο ή μη)
    function showError(field, errorBox, message){
        if(message === ''){
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            errorBox.textContent = '';
            return true;
        }else{
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            errorBox.textContent = message;
            return false;

        }
    }

    // -Έλεγχος πεδίων-

    //έλεγχος τίτλου
    function checkTitle(){
        var box = document.getElementById('titleError');
        var v = title.value.trim();
        var msg = '';

        if (v.length===0)      msg = 'Ο τίτλος είναι υποχρεωτικός';
        else if (v.length<5)   msg = 'Τουλάχιστον 5 χαρακτήρες';
        else if (v.length>100) msg = 'Το πολύ 100 χαρακτήρες';

        return showError(title, box, msg);

    }

    //έλεγχος κατηγορίας
    function checkCategory(){
        var box = document.getElementById('categoryError');
        var msg = '';

        if (category.value === '') msg ='Επιλέξτε κατηγορία';

        return showError(category, box, msg);

    }

    //έλεγχος διεύθυνσης
    function checkAddress(){
        var box = document.getElementById('addressError');
        var v = address.value.trim();
        var msg = '';

        if (v.length === 0)    msg ='Η διεύθυνση είναι υποχρεωτική';
        else if (v.length < 5) msg='Δώστε πιο συγκεκριμένη διεύθυνση';

        return showError(address, box, msg);

    }

    //έλεγχος περιγραφής
    function checkDescription(){
        var box = document.getElementById('descriptionError');
        var v = description.value.trim();
        var msg = '';

        if (v.length===0)       msg= 'Η περιγραφή είναι υποχρεωτική';
        else if (v.length<10)   msg= 'Τουλάχιστον 10 χαρακτήρες';
        else if (v.length>1000) msg= 'Το πολύ 1000 χαρακτήρες';

        return showError(description, box, msg);

    }

    //έλεγχος ονόματος χρήστη
    function checkUsername(){
        var box = document.getElementById('usernameError');

        // αν δεν είναι επώνυμη η υποβολή δεν υπάρχει έλεγχος
        if (!typeNamed.checked){
            username.classList.remove('is-invalid', 'is-valid');
            box.textContent = '';
            return true;
        }

        var v = username.value.trim();
        var msg = '';

        if (v.length===0)     msg= 'Το username είναι υποχρεωτικό';
        else if (v.length<3)  msg= 'Τουλάχιστον 3 χαρακτήρες';
        else if (v.length>50) msg= 'Το πολύ 50 χαρακτήρες';

        return showError(username, box, msg);

    }

    //έλεγχος βίντεο
    function checkVideo(){
        var box = document.getElementById('videoError');

        //το video είναι προαιρετικό
        if (video.files.length === 0){
            video.classList.remove('is-invalid', 'is-valid');
            box.textContent = '';
            return true;
        }

        var file = video.files[0];
        var name = file.name.toLowerCase();
        var msg = '';

        if (name.slice(-4) !== '.mp4')          msg ='Επιτρέπονται μόνο αρχεία .mp4';
        else if (file.size > 20 * 1024 * 1024)  msg ='Το αρχείο ξεπερνά τα 20MB';

        return showError(video, box, msg);

    }

    //έλεγχος τύπουusername (επώνυμο - ανώνυμο)
    function checkType() {
        if (typeNamed.checked || typeAnon.checked){
            typeError.classList.add('d-none');
            return true;
        } else {
            typeError.classList.remove('d-none');
            return false;
        }
    }

    //έλεγχος αν όλα τα πεδία έχουν ορθές τιμές
    function allFieldsOk(){
        if (title.value.trim().length<5)          return false;
        if (title.value.trim().length>100)        return false;
        if (category.value === '')                return false;
        if (address.value.trim().length<5)        return false;
        if (description.value.trim().length<10)   return false;
        if (description.value.trim().length>1000) return false;

        if (!typeNamed.checked && !typeAnon.checked) return false;
        if (typeNamed.checked && username.value.trim().length < 3) return false;

        if (video.files.length>0){
            if (video.files[0].size > 20 * 1024 * 1024) return false;
        }

        return true;

    }

    //μετρητής χαρακτήρων
    function update_chars() {
        document.getElementById('titleCount').textContent = title.value.length;
        document.getElementById('descCount').textContent  = description.value.length;

        if (typeNamed.checked) {
            userWrapper.classList.remove('d-none');
        } else {
            userWrapper.classList.add('d-none');
            username.value = '';
        }

        submitBtn.disabled = !allFieldsOk(); //απενεργ. κουμπί υποβολής

    }

    //χρωματισμός του πεδίου αν μείνει κενό
    title.addEventListener('blur', checkTitle);
    category.addEventListener('blur', checkCategory);
    address.addEventListener('blur', checkAddress);
    description.addEventListener('blur', checkDescription);
    username.addEventListener('blur', checkUsername);
    video.addEventListener('blur', checkVideo);

    //αν το πεδίο είναι κόκκινο, επανέλεγχος ορθότητας
    title.addEventListener('input', function () {
        if (title.classList.contains('is-invalid')) checkTitle();  });
    address.addEventListener('input', function () {
        if (address.classList.contains('is-invalid')) checkAddress();  });
    description.addEventListener('input', function () {
        if (description.classList.contains('is-invalid')) checkDescription();  });
    username.addEventListener('input', function () {
        if (username.classList.contains('is-invalid')) checkUsername();  });

    // μετρητής χαρακτ.
    form.addEventListener('input',  update_chars);
    form.addEventListener('change', update_chars);

    // επαναφορά σε αρχική κατάσταση
    document.getElementById('titleCount').textContent= title.value.length;
    document.getElementById('descCount').textContent= description.value.length;

    if (typeNamed.checked) { //για αφαίρεση κλάσης σε περίπτωση που δεν είναι επιλεγμένο το username
        userWrapper.classList.remove('d-none');
    }

    //για επιτυχή υποβολή, καθαρισμός φόρμας
    if (document.querySelector('.alert-success')) {
        form.reset();
        userWrapper.classList.add('d-none');
        document.getElementById('titleCount').textContent = '0';
        document.getElementById('descCount').textContent  = '0';
        submitBtn.disabled = true;
    }
    
});
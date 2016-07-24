var form = document.getElementById('mailform'),
    formMsg = document.getElementById('form-msg'),
    requiredFields = form.getElementsByClassName('required');

form.addEventListener('submit',function(e){
    e.preventDefault();

    formMsg.style.display = 'none';
    formMsg.className = 'alert';

    var errorOccured = false;
    for (var i = requiredFields.length-1; i >= 0; i--) {
        var that = requiredFields[i],
            parent = that.parentNode;

        if (that.value === '') {
            parent.classList.add('has-error');
            errorOccured = true;
        } else {
            if(parent.classList.contains('has-error')) {
                parent.classList.remove('has-error');
            }
        }
    }

    if(errorOccured === true) {
        formMsg.innerHTML = formMsg.getAttribute('data-required');
        formMsg.style.display = 'block';
        formMsg.className = 'alert alert-danger';
        return false;
    }

    var xmlhttp;
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function(){
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
            console.log(xmlhttp.responseText);
            response = JSON.parse(xmlhttp.responseText);
            if (response.status === 1) {
                formMsg.innerHTML = response.msg;
                formMsg.style.display = 'block';
                formMsg.className = 'alert alert-success';
                form.parentNode.removeChild(form);
            } else {
                formMsg.innerHTML = response.msg;
                formMsg.style.display = 'block';
                formMsg.className = 'alert alert-danger';
            }
        }
    }
    xmlhttp.open("POST", '/wp-admin/admin-ajax.php?action=sendform', true);
    xmlhttp.send(serialize(form));

    return false;
});

function serialize(form) {
    var i,
        j,
        q = [],
        fd = new FormData();

    for (i = form.elements.length - 1; i >= 0; i = i - 1) {
        if (form.elements[i].name === "") {
            continue;
        }
        switch (form.elements[i].nodeName) {
        case 'INPUT':
            switch (form.elements[i].type) {
                case 'text':
                case 'hidden':
                case 'email':
                case 'number':
                case 'password':
                    fd.append(form.elements[i].name, form.elements[i].value);
                    break;
                case 'checkbox':
                case 'radio':
                    if (form.elements[i].checked) {
                        fd.append(form.elements[i].name, form.elements[i].value);
                    }
                    break;
                case 'file':
                    fd.append(form.elements[i].name, form.elements[i].files[0])
                    break;
            }
            break;
        case 'TEXTAREA':
            fd.append(form.elements[i].name, form.elements[i].value);
            break;
        case 'SELECT':
            switch (form.elements[i].type) {
            case 'select-one':
                fd.append(form.elements[i].name, form.elements[i].value);
                break;
            case 'select-multiple':
                for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                    if (form.elements[i].options[j].selected) {
                        fd.append(form.elements[i].name, form.elements[i].options[j].value);
                    }
                }
                break;
            }
            break;
        }
    }
    return fd;
}

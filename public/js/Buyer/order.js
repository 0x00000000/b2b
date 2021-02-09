function addToBasket(code, count) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', rootUrl + '/order/buy/' + code + '/' + count);
    xhr.send();

    xhr.onload = function() {
        if (xhr.status == 200) {
            try {
                var response = JSON.parse(xhr.response);
            } catch (e) {
                var response = null;
            }
            if (response && response.basket && ! response.error) {
                fillBacket(response);
            }
        }
    };
    
    xhr.onerror = function() {
        document.getElementById('error').style.setProperty('display', 'block');
    };
}
function loadBasket() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', rootUrl + '/order/get');
    xhr.send();

    xhr.onload = function() {
        if (xhr.status == 200) {
            try {
                var response = JSON.parse(xhr.response);
            } catch (e) {
                var response = null;
            }
            if (response && response.basket && ! response.error) {
                fillBacket(response);
                if (response.basket.count) {
                    var form = renderForm(response.basket.order);
                    document.getElementById('form').innerHTML = '';
                    document.getElementById('form').appendChild(form);
                }
            }
        }
    };
    
    xhr.onerror = function() {
        document.getElementById('error').style.setProperty('display', 'block');
    };
}
function fillBacket(response) {
    var count = response.basket ? response.basket.count : 0;
    var cost = response.basket ? response.basket.cost : 0;
    if (count) {
        document.getElementById('basket').innerHTML = 'In your basket ' + count + ' products. Total cost is ' + cost;
        var table = renderTable(response.basket.order.products);
        document.getElementById('table').innerHTML = '';
        document.getElementById('table').appendChild(table);
    } else {
        document.getElementById('basket').innerHTML = 'You basket is empty';
        document.getElementById('table').innerHTML = '';
    }
}
function renderTable(productData) {
    function createAddToBasketCallback(code, count) {
        var localCode = code;
        var localCoount = count;
        return function() {addToBasket(localCode, localCoount); return false;}
    }
    
    if (productData) {
        var table = document.createElement('table');
        var th, tr, td, a, span;
        table.className = 'listTable';
        tr = document.createElement('tr');
        th = document.createElement('th');
        th.innerHTML = 'Код товара';
        tr.appendChild(th);
        th = document.createElement('th');
        th.innerHTML = 'Название';
        tr.appendChild(th);
        th = document.createElement('th');
        th.innerHTML = 'Цена';
        tr.appendChild(th);
        th = document.createElement('th');
        th.innerHTML = 'Стоимость';
        tr.appendChild(th);
        th = document.createElement('th');
        th.innerHTML = 'Количество';
        tr.appendChild(th);
        table.appendChild(tr);
        for (var key in productData) {
            var product = productData[key];
            tr = document.createElement('tr');
            td = document.createElement('td');
            td.innerHTML = key;
            tr.appendChild(td);
            td = document.createElement('td');
            if (product.link) {
                a = document.createElement('a');
                a.href = product.link;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                a.innerHTML = product.caption;
                td.appendChild(a);
            } else {
                td.innerHTML = product.caption;
            }
            tr.appendChild(td);
            td = document.createElement('td');
            td.innerHTML = product.price;
            tr.appendChild(td);
            td = document.createElement('td');
            td.innerHTML = product.cost;
            tr.appendChild(td);
            
            td = document.createElement('td');
            a = document.createElement('a');
            a.className = 'orderDecrease';
            a.href = '';
            a.onclick = createAddToBasketCallback(key, -1);
            a.innerHTML = '-';
            td.appendChild(a);
            span = document.createElement('span');
            span.innerHTML = product.count;
            td.appendChild(span);
            a = document.createElement('a');
            a.className = 'orderIncrease';
            a.href = '';
            a.onclick = createAddToBasketCallback(key, 1);
            a.innerHTML = '+';
            td.appendChild(a);
            tr.appendChild(td);
            table.appendChild(tr);
        }
    } else {
        var table = document.createElement('span');
    }
    return table;
}

function renderForm(orderData) {
    function createInput(caption, name, orderData) {
        var tr, td, label, input;
        tr = document.createElement('tr');
        td = document.createElement('td');
        label = document.createElement('label');
        label.innerHTML = caption;
        td.appendChild(label);
        tr.appendChild(td);
        td = document.createElement('td');
        input = document.createElement('input');
        input.name = name;
        if (orderData[name]) {
            input.value = orderData[name];
        }
        td.appendChild(input);
        tr.appendChild(td);
        return tr;
    }
    
    var input, label, tr, td;
    var form = document.createElement('form');
    form.action = rootUrl + '/order';
    form.method = 'POST';
    var table = document.createElement('table');
    table.className = 'formTable';
    tr = createInput('Name', 'name', orderData);
    table.appendChild(tr);
    tr = createInput('Surname', 'surname', orderData);
    table.appendChild(tr);
    tr = createInput('Patronymic', 'patronymic', orderData);
    table.appendChild(tr);
    tr = createInput('Phone', 'phone', orderData);
    table.appendChild(tr);
    tr = createInput('Email', 'email', orderData);
    table.appendChild(tr);
    tr = createInput('Address', 'address', orderData);
    table.appendChild(tr);
    tr = createInput('Comment', 'comment', orderData);
    table.appendChild(tr);
    
    tr = document.createElement('tr');
    td = document.createElement('td');
    tr.appendChild(td);
    td = document.createElement('td');
    input = document.createElement('input');
    input.type = 'submit';
    input.name = 'submit';
    input.value = 'Send';
    td.appendChild(input);
    tr.appendChild(td);
    table.appendChild(tr);
    
    form.appendChild(table);
    
    return form;
}


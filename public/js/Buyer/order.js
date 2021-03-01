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

function changeCountInBasket(code, count) {
    var originInput = document.getElementById('orderCountOrig' + code);
    if (originInput && typeof originInput.value !== undefined) {
        var origCount = parseInt(originInput.value);
        var diffCount = count - origCount;
        if (diffCount) {
            addToBasket(code, diffCount)
        }
    }
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
        document.getElementById('basket').style.display = 'block';
        document.getElementById('basketEmpty').style.display = 'none';
        document.getElementById('basketCount').innerHTML = count;
        document.getElementById('basketCost').innerHTML = cost;
        var table = renderTable(response.basket.order.products);
        document.getElementById('table').innerHTML = '';
        document.getElementById('table').appendChild(table);
    } else {
        document.getElementById('basket').style.display = 'none';
        document.getElementById('basketEmpty').style.display = 'block';
        document.getElementById('table').innerHTML = '';
    }
}
function renderTable(productData) {
    function createAddToBasketCallback(code, count) {
        var localCode = code;
        var localCoount = count;
        return function() {addToBasket(localCode, localCoount); return false;}
    }
    
    function createChangeCountInBasketCallback(code) {
        var localCode = code;
        return function() {changeCountInBasket(localCode, this.value); return false;}
    }
    
    var totalCost = 0;
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
            totalCost += Number(product.cost);
            
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
            td.className = 'orderChangeCount'
            /*a = document.createElement('a');
            a.className = 'orderDecrease';
            a.href = '';
            a.onclick = createAddToBasketCallback(key, -1);
            a.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;';
            td.appendChild(a);*/
            input = document.createElement('input');
            input.className = 'orderCount';
            input.value = product.count;
            input.onchange = createChangeCountInBasketCallback(key);
            td.appendChild(input);
            input = document.createElement('input');
            input.type = 'hidden';
            input.value = product.count;
            input.id = 'orderCountOrig' + key;
            td.appendChild(input);
            input = document.createElement('input');
            input.type = 'button';
            input.value = 'OK';
            input.className = 'orderCountSet';
            td.appendChild(input);
            /*a = document.createElement('a');
            a.className = 'orderIncrease';
            a.href = '';
            a.onclick = createAddToBasketCallback(key, 1);
            a.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;+&nbsp;&nbsp;&nbsp;&nbsp;';
            td.appendChild(a);*/
            tr.appendChild(td);
            table.appendChild(tr);
        }
        
        tr = document.createElement('tr');
        td = document.createElement('td');
        td.style.fontWeight = 'bold';
        td.innerHTML = 'Итого';
        tr.appendChild(td);
        td = document.createElement('td');
        td.colspan = '4';
        td.className = 'alignLeft';
        td.style.fontWeight = 'bold';
        td.innerHTML = totalCost.toFixed(2);
        tr.appendChild(td);
        table.appendChild(tr);
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
    tr = createInput('Коментарий', 'comment', orderData);
    table.appendChild(tr);
    
    tr = document.createElement('tr');
    td = document.createElement('td');
    tr.appendChild(td);
    td = document.createElement('td');
    input = document.createElement('input');
    input.type = 'submit';
    input.name = 'submit';
    input.value = 'Отправить';
    td.appendChild(input);
    tr.appendChild(td);
    table.appendChild(tr);
    
    form.appendChild(table);
    
    return form;
}


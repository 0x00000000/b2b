function setDisabledToControls(value) {
    let inputList = document.getElementsByClassName('orderCount');
    for (let key in inputList) {
        inputList[key].disabled = value;
    }
    inputList = document.getElementsByClassName('orderDecrease');
    for (let key in inputList) {
        inputList[key].disabled = value;
    }
    inputList = document.getElementsByClassName('orderIncrease');
    for (let key in inputList) {
        inputList[key].disabled = value;
    }
}

function addToBasket(code, count, minCount) {
    let originInput = document.getElementById('orderCountOrig' + code);
    if (originInput && typeof originInput.value !== 'undefined' && parseInt(originInput.value) + count >= minCount) {
        sendRequest('/order/buy/' + code + '/' + count);
    }
}

function clearBasket() {
    if (confirm('Очистить корзину?')) {
        sendRequest('/order/clear');    
    }
    return false;
}

function sendRequest(url) {
    formSender.disallowToSend();
    setDisabledToControls(true);
    
    let xhr = new XMLHttpRequest();
    xhr.open('GET', rootUrl + url);
    xhr.send();

    xhr.onload = function() {
        if (xhr.status == 200) {
            let response;
            try {
                response = JSON.parse(xhr.response);
            } catch (e) {
                response = null;
            }
            if (response && response.basket && ! response.error) {
                fillBacket(response);
            }
        }
        
        setDisabledToControls(false);
        formSender.allowToSend();
        formSender.sendIfNeeded();
    };
    
    xhr.onerror = function() {
        setDisabledToControls(false);
        formSender.allowToSend();
        formSender.sendIfNeeded();
    };
}

function changeCountInBasket(code, count) {
    let originInput = document.getElementById('orderCountOrig' + code);
    if (originInput && typeof originInput.value !== undefined) {
        let regexp = /^\s*\d+\s*$/;
        if (typeof count === 'string' && ! regexp.test(count)) {
            alert('Введите корректное количество целым числом');
            document.getElementById('orderCount' + code).value = originInput.value;
            setTimeout(function() {
                document.getElementById('orderCount' + code).focus();
            }, 0);
            return;
        }
        
        count = parseInt(count);
        if (count < 0 || isNaN(count)) {
            alert('Введите корректное количество целым числом');
            document.getElementById('orderCount' + code).value = originInput.value;
            setTimeout(function() {
                document.getElementById('orderCount' + code).focus();
            }, 0);
            return;
        }
        
        let origCount = parseInt(originInput.value);
        let diffCount = count - origCount;
        if (diffCount) {
            addToBasket(code, diffCount, 0)
        }
    }
}

function loadBasket() {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', rootUrl + '/order/get');
    xhr.send();
    
    
    xhr.onload = function() {
        if (xhr.status == 200) {
            let response;
            try {
                response = JSON.parse(xhr.response);
            } catch (e) {
                response = null;
            }
            if (response && response.basket && ! response.error) {
                fillBacket(response);
                if (response.basket.count) {
                    let form = renderForm(response.basket.order);
                    document.getElementById('formBox').innerHTML = '';
                    document.getElementById('formBox').appendChild(form);
                }
            }
        }
    };
    
    xhr.onerror = function() {
        document.getElementById('error').style.setProperty('display', 'block');
    };
}

function fillBacket(response) {
    let count = response.basket ? response.basket.count : 0;
    let cost = response.basket ? response.basket.cost : 0;
    if (count) {
        document.getElementById('basket').style.display = 'block';
        document.getElementById('basketEmpty').style.display = 'none';
        document.getElementById('basketCount').innerHTML = count;
        document.getElementById('basketCost').innerHTML = cost;
        if (document.getElementById('totalCost')) { // If basket is loaded.
            document.getElementById('totalCost').innerHTML = cost;
        }
        if (response.basket.order) {
            let table = renderTable(response.basket.order.products);
            document.getElementById('table').innerHTML = '';
            document.getElementById('table').appendChild(table);
        } else {
            changeProductCount(response.basket.changedProduct);
        }
    } else {
        document.getElementById('basket').style.display = 'none';
        document.getElementById('basketEmpty').style.display = 'block';
        document.getElementById('table').innerHTML = '';
        document.getElementById('table').innerHTML = '';
        document.getElementById('formBox').innerHTML = '';
        document.getElementById('orderCaption').style.display = 'none';
    }
}

function changeProductCount(productData) {
    if (productData && productData.code && productData.count !== undefined) {
        let code = productData.code;
        let count = parseInt(productData.count);
        
        let originInput = document.getElementById('orderCountOrig' + code);
        let visibleInput = document.getElementById('orderCount' + code);
        if (originInput && visibleInput) {
            if (count) {
                originInput.value = count;
                visibleInput.value = count;
                document.getElementById('orderCost' + code).innerHTML = numberFormat(count * document.getElementById('orderPrice' + code).innerHTML);
            } else {
                let tr = originInput.parentElement.parentElement;
                tr.parentElement.removeChild(tr);
            }
        }
    }
}

function numberFormat(value) {
    return Math.round(value * 100) / 100;
}

function removeProductFromBasket(code) {
    if (confirm('Удалить товар из корзины?')) {
        changeCountInBasket(code, 0);
    }
}

function renderTable(productData) {
    function createAddToBasketCallback(code, count, minCount) {
        let localCode = code;
        let localCoount = count;
        return function() {addToBasket(localCode, localCoount, minCount); return false;}
    }
    
    function createChangeCountInBasketCallback(code) {
        let localCode = code;
        return function() {changeCountInBasket(localCode, this.value); return false;}
    }
    
    function createDeleteFromBasketCallback(code) {
        let localCode = code;
        return function() {removeProductFromBasket(localCode); return false;}
    }
    
    let table;
    if (productData) {
        let totalCost = 0;
        let th, tr, td, div, a, span;
        table = document.createElement('table');
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
        th.innerHTML = 'Сумма';
        tr.appendChild(th);
        th = document.createElement('th');
        th.innerHTML = 'Количество';
        tr.appendChild(th);
        table.appendChild(tr);
        for (let key in productData) {
            let product = productData[key];
            totalCost += Number(product.cost);
            
            tr = document.createElement('tr');
            td = document.createElement('td');
            td.innerHTML = key;
            tr.appendChild(td);
            td = document.createElement('td');
            td.className = 'alignLeft';
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
            td.id = 'orderPrice' + key;
            td.innerHTML = product.price;
            tr.appendChild(td);
            td = document.createElement('td');
            td.id = 'orderCost' + key;
            td.innerHTML = product.cost;
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.className = 'orderChangeCount singleLine';
            input = document.createElement('input');
            input.type = 'button';
            input.className = 'orderDecrease';
            input.onclick = createAddToBasketCallback(key, -1, 1);
            input.value = '-';
            td.appendChild(input);
            input = document.createElement('input');
            input.className = 'orderCount';
            input.id = 'orderCount' + key;
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
            input.className = 'orderIncrease';
            input.onclick = createAddToBasketCallback(key, 1, 1);
            input.value = '+';
            td.appendChild(input);
            
            div = document.createElement('div');
            div.className = 'orderDeleteBox';
            a = document.createElement('a');
            a.href = '';
            a.innerHTML = 'Удалить';
            a.className = 'orderDelete';
            a.onclick = createDeleteFromBasketCallback(key);
            div.appendChild(a);
            td.appendChild(div);
            tr.appendChild(td);
            table.appendChild(tr);
        }
        
        tr = document.createElement('tr');
        td = document.createElement('td');
        td.style.fontWeight = 'bold';
        td.innerHTML = 'Итого';
        tr.appendChild(td);
        td = document.createElement('td');
        td.colSpan = '3';
        td.className = 'alignLeft';
        td.id = 'totalCost';
        td.style.fontWeight = 'bold';
        td.innerHTML = totalCost.toFixed(2);
        tr.appendChild(td);
        td = document.createElement('td');
        td.className = 'orderChangeCount singleLine';
        a = document.createElement('a');
        a.href = '';
        a.innerHTML = 'Очистить корзину';
        a.className = 'orderClear';
        a.onclick = clearBasket;
        td.appendChild(a);
        tr.appendChild(td);
        table.appendChild(tr);
    } else {
        table = document.createElement('span');
    }
    return table;
}

function renderForm(orderData) {
    let form;
    if (orderData.products) {
        function createInput(caption, name, orderData) {
            let tr, td, label, input;
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
        
        let input, label, tr, td;
        form = document.createElement('form');
        form.action = rootUrl + '/order';
        form.method = 'POST';
        form.id = 'form';
        form.onsubmit = function(){formSender.sendIfCan(); return false;};
        let table = document.createElement('table');
        table.className = 'formTable';
        tr = createInput('Коментарий', 'comment', orderData);
        table.appendChild(tr);
        
        tr = document.createElement('tr');
        td = document.createElement('td');
        tr.appendChild(td);
        td = document.createElement('td');
        input = document.createElement('input');
        input.type = 'button';
        input.name = 'save';
        input.value = 'Отправить заказ';
        input.onclick = function(){if (confirm('Отправить заказ?')) {document.getElementById('form').submit();}};
        td.appendChild(input);
        tr.appendChild(td);
        table.appendChild(tr);
        
        form.appendChild(table);
    } else {
        form = document.createElement('span');
    }
    
    return form;
}


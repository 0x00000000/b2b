function addToBasket(code, count, link) {
    let regexp = /^\s*\d+\s*$/;
    if (typeof count === 'string' && ! regexp.test(count)) {
        alert('Введите корректное количество целым числом');
        document.getElementById('productCoount' + code).focus();
        return;
    }
    
    count = parseInt(count);
    if (count <= 0 || isNaN(count)) {
        alert('Введите корректное количество целым числом');
        document.getElementById('productCoount' + code).focus();
        return;
    }
    
    let xhr = new XMLHttpRequest();
    xhr.open('GET', rootUrl + '/order/buy/' + code + '/' + count);
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
                link.style.display = 'none';
                let count = response.basket ? response.basket.count : 0;
                let cost = response.basket ? response.basket.cost : 0;
                if (count) {
                    document.getElementById('basket').style.display = 'block';
                    document.getElementById('basketEmpty').style.display = 'none';
                    document.getElementById('basketCount').innerHTML = count;
                    document.getElementById('basketCost').innerHTML = cost;
                } else {
                    document.getElementById('basket').style.display = 'none';
                    document.getElementById('basketEmpty').style.display = 'block';
                }
            }
        }
    };
    xhr.onerror = function() {
        document.getElementById('error').style.setProperty('display', 'block');
    };
    
}

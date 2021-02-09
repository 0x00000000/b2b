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
                var count = response.basket ? response.basket.count : 0;
                var cost = response.basket ? response.basket.cost : 0;
                if (count) {
                    document.getElementById('basket').innerHTML = 'In your basket ' + count + ' products. Total cost is ' + cost;
                } else {
                    document.getElementById('basket').innerHTML = 'You basket is empty';
                }
            }
        }
    };
    
    xhr.onerror = function() {
        document.getElementById('error').style.setProperty('display', 'block');
    };
}

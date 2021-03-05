function addToBasket(code, count, link) {
    var xhr = new XMLHttpRequest();
    count = parseInt(count);
    if (count <= 0) {
        count = 1;
    }
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
                link.style.display = 'none';
                var count = response.basket ? response.basket.count : 0;
                var cost = response.basket ? response.basket.cost : 0;
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

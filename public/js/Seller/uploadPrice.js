function uploadPrice(productsCount, productsUploadCount) {
    sendSavePriceRequest(1);
    
    function sendSavePriceRequest(from) {
        let progressWidth = Math.ceil((from + productsUploadCount) * 100 / productsCount);
        if (progressWidth > 100) {
            progressWidth = 100;
        }
        let progressLeftElement = document.getElementById('progressLeft');
        progressLeftElement.style.setProperty('width', progressWidth + '%');
        
        let xhr = new XMLHttpRequest();
        xhr.open('GET', window.location.href + '/savePrice/' + from);
        xhr.send();

        xhr.onload = function() {
            if (xhr.status == 200) {
                let response = JSON.parse(xhr.response);
                if (response && ! response.error && response.finished) {
                    document.getElementById('success').style.setProperty('display', 'block');
                } else {
                    sendSavePriceRequest(from + productsUploadCount);
                }
            } else {
                document.getElementById('error').style.setProperty('display', 'block');
            }
        };
        
        xhr.onerror = function() {
            document.getElementById('error').style.setProperty('display', 'block');
        };
    }
};

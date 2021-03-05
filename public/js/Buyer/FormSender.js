function FormSender() {
    var _canSend = true;
    var _shouldSend = false;
    var _sendCallback = undefined;
    
    this.setSendCallback = function(callback) {
        if (typeof callback === 'function') {
            _sendCallback = callback;
        }
    }
    
    this.allowToSend = function() {
        _canSend = true;
    }
    
    this.disallowToSend = function() {
        _canSend = false;
    }
    
    this.sendIfCan = function() {
        if (_canSend && _sendCallback) {
            _shouldSend = false;
            return _sendCallback();
        } else {
            _shouldSend = true;
        }
    }
    
    this.sendIfNeeded = function() {
        if (_shouldSend && _sendCallback) {
            _shouldSend = false;
            return _sendCallback();
        }
    }
}

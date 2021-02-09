function ActiveLesson(isTeacher, lessonParams) {

var whiteBoard;
var application;
var nicEdit;

var bkLibReady = false;
var documentReady = false;

if (! lessonParams)
    return;

if (typeof lessonParams['requestUrl'] === 'undefined')
    return;

bkLib.onDomLoaded(function() {
    var iconsPath = (typeof rootUrl !== 'undefined' ? rootUrl : '')
        + '/vendor/nicEdit/nicEditorIcons.gif';
    var nicEditParams = {
            maxHeight : 400,
            iconsPath: iconsPath,
            buttonList: ['bold','italic','underline','forecolor']};
    nicEdit = new nicEditor(nicEditParams).panelInstance('lessonNicEditPanel');
    var editorContainer = document.getElementById('lessonNicEditPanelContainer');
    if (editorContainer) {
        whiteBoard = editorContainer.querySelector('.nicEdit-main');
        whiteBoard.classList.add('whiteBoard');
    }
    if (whiteBoard) {
        bkLibReady = true;
        initApplication();
    }
});

$(document).ready(function() {
    documentReady = true;
    initApplication();
});

function initApplication() {
    if (documentReady && bkLibReady) {
        Params.init(lessonParams);
        initWhiteBoard();
        application = new Application(isTeacher, Params.requestUrl);
        application.start();
    }
}

var Params = function() {
}
Params.requestUrl = '';
Params.lockTime = 3000;
Params.reloadTimeout = 1000;
Params.pointerLifeTime = 7000;
Params.init = function(params) {
    Params.requestUrl = params['requestUrl'];
    if (params['lockTime']) {
        Params.lockTime = params['lockTime'];
    }
    if (params['reloadTimeout']) {
        Params.reloadTimeout = params['reloadTimeout'];
    }
    if (params['pointerLifeTime']) {
        Params.pointerLifeTime = params['pointerLifeTime'];
    }
};

function initWhiteBoard() {
    
    function setWhiteBoardHeight() {
        var el = $('.whiteBoard').parent();
        if (el.length) {
            var box = el.get(0).getBoundingClientRect();
            var fromTop = box.top + pageYOffset;
            var bordersHeight = 2;
            var height = Math.floor(document.documentElement.clientHeight - fromTop - bordersHeight);
            el.get(0).style.height = height + 'px';
            el.get(0).style.maxHeight = height + 'px';
        }
    }
    
    $(window).on('resize', function() {
        setWhiteBoardHeight();
    });
    
    setWhiteBoardHeight();
}

function Command(params) {
    this._data = params;
    this._type = 'Command';
    this._isSingle = true;
}

Command.prototype.debug = function(caption) {
    var today = new Date;
    console.log((caption ? caption : '') + 'command (' + this.getType() + ') ' + today.getHours() + '-' + today.getMinutes() + '-' + today.getSeconds() + ':', JSON.stringify(this.getData()));
}

Command.prototype.execute = function() {
    console.log('Base command execute data', this.getData());
}

Command.prototype.isEqualTo = function(command) {
    var result = false;
    
    if (command instanceof Command) {
        var data1 = command.getData();
        var data2 = this.getData();
        if (data1 && data2) {
            result = this.isDataEqual(data1, data2);
        } else {
            if (data1 === data2) {
                result = true;
            }
        }
    }
    
    return result;
}

Command.prototype.isDataEqual = function(data1, data2) {
    return JSON.stringify(data1) === JSON.stringify(data2);
}

Command.prototype.getData = function() {
    return this._data;
}

Command.prototype.getType = function() {
    return this._type;
}

Command.prototype.serialize = function() {
    var data = {
        type: this._type,
        params: this._data
    };
    
    return JSON.stringify(data);
}

Command.prototype.isSingle = function() {
    return this._isSingle;
}

function CommandFactory() {
}

CommandFactory.create = function(serialized) {
    var command = null;
    
    if (typeof serialized === 'string') {
        try {
            var data = JSON.parse(serialized);
            if (
                data
                && typeof data['type'] !== 'undefined'
                && typeof data['params'] !== 'undefined'
            ) {
                switch (data['type']) {
                    case 'ScrollCommand':
                        command = new ScrollCommand(data['params']);
                        break;
                    case 'ClickCommand':
                        command = new ClickCommand(data['params']);
                        break;
                    case 'EditCommand':
                        command = new EditCommand(data['params']);
                        break;
                    default:
                        break;
                }
            }
        } catch (e) {
            console.log('Unrecognazible command', e);
        }
        
        return command;
    }
}

function ScrollCommand(params) {
    if (typeof params === 'undefined') {
        params = {scroll: whiteBoard.parentNode.scrollTop};
    }
    Command.apply(this, [params]);
    this._type = 'ScrollCommand';
}

ScrollCommand.prototype = Object.create(Command.prototype);
ScrollCommand.prototype.constructor = ScrollCommand;

ScrollCommand.prototype.execute = function() {
    this.debug('execute');
    var data = this.getData();
    if (data && typeof data.scroll != 'undefined') {
        whiteBoard.parentNode.scrollTo({
            top: data.scroll,
            left: 0,
            behavior: 'instant',
        });
    }
}

ScrollCommand.prototype.isDataEqual = function(data1, data2) {
    // Not strict comparing. We count that '100' is equal to 100.
    return data1.scroll == data2.scroll;
}

function ClickCommand(params) {
    Command.apply(this, [params]);
    this._type = 'ClickCommand';
    this._pointerClassName = 'pointer';
    this._isSingle = false;
}

ClickCommand.prototype = Object.create(Command.prototype);
ClickCommand.prototype.constructor = ClickCommand;

ClickCommand.prototype.execute = function() {
    this.debug('execute');
    var self = this;
    var data = self.getData();
    if (data && typeof data.x !== 'undefined'  && typeof data.y !== 'undefined') {
        var pointer = document.createElement('div');
        pointer.className = self._pointerClassName;
        pointer.style.left = data.x + 'px';
        pointer.style.top = data.y + 'px';
        whiteBoard.appendChild(pointer);
        setTimeout(
            function() {
                $(pointer).hide('slow', function() {
                    if (pointer && pointer.remove) {
                        pointer.remove();
                    }
                });
            }, Params.pointerLifeTime
        );
    }
}

ClickCommand.prototype.isDataEqual = function(data1, data2) {
    // Not strict comparing. We count that '100' is equal to 100.
    return data1.x == data2.x && data1.y == data2.y;
}

function EditCommand(params) {
    if (typeof params === 'undefined') {
        params = {content: whiteBoard.innerHTML};
    }
    Command.apply(this, [params]);
    
    if (this._data && typeof this._data.content === 'string') {
        var regExp = /\s*(\s*<div[^>]+class\s*=[\s'"]+pointer[\s'"]+[^>]*>\w*<\/div>\s*)*$/i;
        this._data.content = this._data.content.replace(regExp, '');
    }
    this._type = 'EditCommand';
}

EditCommand.prototype = Object.create(Command.prototype);
EditCommand.prototype.constructor = EditCommand;

EditCommand.prototype.execute = function() {
    this.debug('execute');
    if (this.getData() && this.getData().content) {
        $scrollCommand = new ScrollCommand();
        whiteBoard.innerHTML = this.getData().content;
        $scrollCommand.execute();
    }
}

EditCommand.prototype.isDataEqual = function(data1, data2) {
    return data1.content === data2.content;
}

function LockerPool() {
}

LockerPool._lockers = {};
LockerPool._lockTime = Params.lockTime;

LockerPool.UI = 'ui';
LockerPool.WEB = 'web';

LockerPool.isSet = function(type, subtype) {
    var result = false;
    
    if (type && subtype && LockerPool._lockers[type] && LockerPool._lockers[type][subtype]) {
        var time = (new Date()).getTime();
        result = time < LockerPool._lockers[type][subtype];
        if (! result) {
            LockerPool._lockers[type][subtype] = null;
        }
    }
    
    return result;
}

LockerPool.set = function(type, subtype) {
    var result = false;
    
    if (type && subtype) {
        if (! LockerPool._lockers[type]) {
            LockerPool._lockers[type] = {};
        }
        var time = (new Date()).getTime();
        LockerPool._lockers[type][subtype] = time + LockerPool._lockTime;
        result = true;
    }
    
    return result;
}

LockerPool.clear = function(type, subtype) {
    var result = false;
    
    if (type && subtype) {
        if (! LockerPool._lockers[type]) {
            LockerPool._lockers[type] = {};
        }
        LockerPool._lockers[type][subtype] = null;
        result = true;
    }
    
    return result;
}

function Controller(params) {
    if (typeof params === 'undefined') {
        params = {};
    }
    
    this.innerCommands = {}; // For inner use.
    this.outerCommands = {}; // This commands will be executed.
    this.params = params;
}

Controller.prototype.popCommands = function(type) {
    var result = null;
    
    if (this.innerCommands[type] instanceof Array) {
        result = this.innerCommands[type]; 
        delete this.innerCommands[type];
    }
    
    return result;
}

Controller.prototype.addCommands = function(type, commands) {
    if (type && commands instanceof Array && commands.length) {
        for (var i = 0; i < commands.length; i++) {
            if (commands[i].getType() !== type)
                continue;
            this.addOuterCommand(commands[i]);
        }
    }
}

Controller.prototype.addInnerCommand = function(command) {
    if (! (command instanceof Command))
        return;
    
    var type = command.getType();
    if (command.isSingle()) {
        this.innerCommands[type] = [command];
    } else {
        if (! (this.innerCommands[type] instanceof Array)) {
            this.innerCommands[type] = [];
        }
        this.innerCommands[type].push(command);
    }
}

Controller.prototype.addOuterCommand = function(command) {
    if (! (command instanceof Command))
        return;
    
    var type = command.getType();
    if (command.isSingle()) {
        this.outerCommands[type] = [command];
    } else {
        if (! (this.outerCommands[type] instanceof Array)) {
            this.outerCommands[type] = [];
        }
        this.outerCommands[type].push(command);
    }
}

Controller.prototype.executeCommands = function(callback) {
    for (var type in this.outerCommands) {
        if (this.outerCommands[type] instanceof Array) {
            for (var i = 0; i < this.outerCommands[type].length; i++) {
                this.outerCommands[type][i].execute();
            }
        }
    }
    this.outerCommands = {};
    
    if (typeof callback === 'function') {
        callback(null); // For backward compatibility.
    }
}

function UiController(params) {
    var self = this;
    
    if (typeof params === 'undefined') {
        params = {};
    }
    
    Controller.apply(self, arguments);
    
    self.scrollWaitTime = 5;
    
    self.editWaitTime = 5;
    
    self.lastCommandsList = {};
    self.setLastCommand('ScrollCommand', new ScrollCommand());
    self.setLastCommand('EditCommand', new EditCommand());
    
    // $(window).on('scroll keypress keyup keydown click dbclick mousedown mouseup mousemove mousewheel wheel DOMMouseScroll MozMousePixelScroll', function(event) {
    // event.stopPropagation();
    // event.preventDefault();
    // event.stopImmediatePropagation();
    $(whiteBoard).on('scroll mousewheel wheel DOMMouseScroll MozMousePixelScroll', function(event) {
        if (LockerPool.isSet('ScrollCommand', LockerPool.UI)) {
            event.preventDefault();
        }
    });
    
    $(whiteBoard).on('keydown', function(event) {
        if (LockerPool.isSet('EditCommand', LockerPool.UI)) {
            event.preventDefault();
        } else if (event.originalEvent.keyCode >=33 && event.originalEvent.keyCode <= 40) {
            if (LockerPool.isSet('ScrollCommand', LockerPool.UI)) {
                event.preventDefault();
            }
        }
    });
    
    $(whiteBoard).on('mousedown', function(event) {
        if (LockerPool.isSet('EditCommand', LockerPool.UI)) {
            event.preventDefault(); // Previnting text selection when editor is locked.
        }
        if (event.originalEvent.ctrlKey) {
            var params = {x: event.originalEvent.layerX, y: event.originalEvent.layerY};
            if (params.x > 10 || params.y > 10) { // For preventing false pointers for boubleclick.
                var command = new ClickCommand(params);
                self.addInnerCommand(command);
                command.execute();
            }
            event.preventDefault(); // For preventing text selecting.
        }
    });
    
    nicEdit.addEvent('blur', function() {
        // var command = new EditCommand();
        // self.addInnerCommand(command);
    });
    
}

UiController.prototype = Object.create(Controller.prototype);
UiController.prototype.constructor = UiController;

UiController.prototype.popCommands = function(type) {
    var commands = null;
    
    switch (type) {
        case 'ScrollCommand':
            var command = new ScrollCommand();
            if (this.isLastCommandChanged(type, command)) {
                commands = [command];
                this.setLastCommand(type, command);
            }
            break;
        case 'ClickCommand':
            if (this.innerCommands[type]) {
                commands = this.innerCommands[type];
                delete this.innerCommands[type];
            }
            break;
        case 'EditCommand':
            var command = new EditCommand();
            if (this.isLastCommandChanged(type, command)) {
                commands = [command];
                this.setLastCommand(type, command);
            }
            break;
        default:
            break;
    }
    
    return commands;
}

UiController.prototype.executeCommands = function(callback) {
    for (var type in this.outerCommands) {
        if (this.outerCommands[type] instanceof Array) {
            for (var i = 0; i < this.outerCommands[type].length; i++) {
                this.outerCommands[type][i].execute();
                this.setLastCommand(type, this.outerCommands[type][i]);
            }
        }
    }
    this.outerCommands = {};
    
    if (typeof callback === 'function') {
        callback(null); // For backward compatibility.
    }
}

UiController.prototype.setLastCommand = function(type, command) {
    if (type === 'ScrollCommand' || type === 'EditCommand') {
        this.lastCommandsList[type] = command;
    }
}

UiController.prototype.isLastCommandChanged = function(type, command) {
    var result = true;
    
    if (type === 'ScrollCommand' || type === 'EditCommand') {
        result = ! command.isEqualTo(this.lastCommandsList[type]);
    }
    
    return result;
}

function WebController(params) {
    if (typeof params === 'undefined') {
        params = {};
    }
    
    Controller.apply(this, arguments);
    
    if (typeof params['url'] !== 'undefined') {
        this.url = params['url'];
    } else {
        this.url = null;
    }
}

WebController.prototype = Object.create(Controller.prototype);
WebController.prototype.constructor = WebController;

WebController.prototype.executeCommands = function(callback) {
    var self = this;
    
    if (! self.url) {
        console.log('Url is empty.');
        return;
    }
    
    requestData = {commands: {}};
    for (var type in self.outerCommands) {
        if (self.outerCommands[type] instanceof Array) {
            requestData['commands'][type] = [];
            for (var i = 0; i < self.outerCommands[type].length; i++) {
                requestData['commands'][type].push(self.outerCommands[type][i].serialize());
            }
        }
    }
    self.outerCommands = {};
    
    $.ajax(
        self.url,
        {
            dataType: 'json',
            method: 'POST',
            data: requestData,
            success: function(data) {
                if (data['commands'] instanceof Object) {
                    for (var type in data['commands']) {
                        if (data['commands'][type] instanceof Array) {
                            for (var i = 0; i < data['commands'][type].length; i++) {
                                var command = CommandFactory.create(data['commands'][type][i]);
                                self.addInnerCommand(command);
                            }
                        }
                    }
                }
                
                if (typeof callback === 'function') {
                    callback(self.innerCommands);
                    self.innerCommands = {};
                }
            },
            error: function() {
            },
        }
    );
}

function Strategy() {
}

Strategy.prototype.act = function(uiController, webController) {
    // Do nothing.
}

function TeacherStrategy() {
    Strategy.apply(this, arguments);
}

TeacherStrategy.prototype = Object.create(Strategy.prototype);
TeacherStrategy.prototype.constructor = TeacherStrategy;

TeacherStrategy.prototype.act = function(uiController, webController) {
    var types = ['ScrollCommand', 'ClickCommand', 'EditCommand'];
    var uiCommands = {};
    for (var i = 0; i < types.length; i++) {
        uiCommands[types[i]] = uiController.popCommands(types[i]);
        if (uiCommands[types[i]]) {
            webController.addCommands(types[i], uiCommands[types[i]]);
            
            if (types[i] === 'ScrollCommand' || types[i] === 'EditCommand') {
                LockerPool.set(types[i], LockerPool.WEB);
            }
        }
    }
    
    webController.executeCommands(function(webCommands) {
        if (! webCommands)
            return;
        
        var types = ['ScrollCommand', 'ClickCommand', 'EditCommand'];
        for (var i = 0; i < types.length; i++) {
            if (! LockerPool.isSet(types[i], LockerPool.WEB)) {
                if (webCommands[types[i]]) {
                    uiController.addCommands(types[i], webCommands[types[i]]);
                }
            }
        }
        
        uiController.executeCommands();
    });
}

function StudentStrategy() {
    Strategy.apply(this, arguments);
}

StudentStrategy.prototype = Object.create(Strategy.prototype);
StudentStrategy.prototype.constructor = StudentStrategy;

StudentStrategy.prototype.act = function(uiController, webController) {
    var types = ['ScrollCommand', 'ClickCommand', 'EditCommand'];
    var uiCommands = {};
    for (var i = 0; i < types.length; i++) {
        uiCommands[types[i]] = uiController.popCommands(types[i]);
        if (uiCommands[types[i]]) {
            webController.addCommands(types[i], uiCommands[types[i]]);
        }
    }
    
    webController.executeCommands(function(webCommands) {
        if (! webCommands)
            return;
        
        for (var i = 0; i < types.length; i++) {
            if (webCommands[types[i]]) {
                uiController.addCommands(types[i], webCommands[types[i]]);
                
                if (types[i] === 'ScrollCommand' || types[i] === 'EditCommand') {
                    LockerPool.set(types[i], LockerPool.UI);
                }
            }
        }
        
        uiController.executeCommands();
    });
}

function Application(isTeacher, requestUrl) {
    this.isTeacher = isTeacher;
    var uiParams = {};
    this.uiController = new UiController(uiParams);
    var webParams = {url: requestUrl};
    this.webController = new WebController(webParams);
    if (isTeacher) {
        this.strategy = new TeacherStrategy();
    } else {
        this.strategy = new StudentStrategy();
    }
    
    this.active = false;
}


Application.prototype.setStrategy = function(strategy) {
    if (strategy instanceof Strategy) {
        this.Strategy = strategy;
    }
}

Application.prototype.start = function() {
    this.active = true;
    this.act();
}

Application.prototype.stop = function() {
    this.active = false;
}

Application.prototype.act = function() {
    if (this.active) {
        var self = this;
        this.strategy.act(this.uiController, this.webController);
        setTimeout(
            function() {self.act()},
            Params.reloadTimeout
        );
    }
}

}

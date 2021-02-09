window.addEventListener('load', function() {
    var editor;
    
    editor = ContentTools.EditorApp.get();
    editor.init('*[data-editable]', 'data-name');
    // editor.ToolShelf.fetch('undo');
    // ContentTools.DEFAULT_TOOLS[0]

    // ContentTools.StylePalette.add([
    //     new ContentTools.Style('Author', 'author', ['p'])
    // ]);

    editor.addEventListener('saved', function (ev) {
        var name, payload, regions, xhr;
        
        // Check that something changed
        regions = ev.detail().regions;
        if (Object.keys(regions).length == 0) {
            return;
        }
        
        // Set the editor as busy while we save our changes
        this.busy(true);
        
        // Collect the contents of each region into a FormData instance
        payload = new FormData();
        for (name in regions) {
            var elementName = name.replace(/-html$/, '');
            var elements = document.getElementsByName(elementName);
            if (elements.length === 1) {
                elements[0].value = regions[name];
            }
        }
        
        editor.busy(false);
    });
    
});


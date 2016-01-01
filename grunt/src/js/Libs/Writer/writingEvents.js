// ##############################################################################
// FILE: Libs/Writer/writingEvents.js
// ##############################################################################

/*-------------------------------------------------------------
**  Initialize writer events
--------------------------------------------------------------*/
KansoWriter.prototype.initWriterEvents = function() {
    var self = this;

    // continue lists when enter is pressed
    this.writer.on('keyup', function() {
        if (event.keyCode == 13) {
            self.checkForLists(self)
        }
    });
}

/*-------------------------------------------------------------
**  Continue lists when enter is pressed
--------------------------------------------------------------*/
KansoWriter.prototype.checkForLists = function(self) {
    var prevLine = self.writer.getCursor().line - 1;
    var lineText = self.writer.getLine(prevLine);
    var numListRgx = new RegExp('^\\d+\.\\s+');
    var currLine = prevLine + 1;

    if (lineText === '') return;

    // is this an unordered list
    if ((lineText !== '') && (lineText[0]) && (lineText[0] === '-' || lineText[0] === '+' || lineText[0] === '*') && (lineText[1]) && (lineText[1] === "" || lineText[1] === " ")) {
        toInsert = lineText[0] + ' ';
        self.writer.replaceRange(toInsert, {
            line: currLine,
            ch: 0
        });
    } else if (numListRgx.test(lineText)) {
        num = parseInt(lineText[0]) + 1;
        toInsert = num + '. ';
        self.writer.replaceRange(toInsert, {
            line: currLine,
            ch: 0
        });
    }
}

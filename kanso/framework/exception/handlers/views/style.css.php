<style>
	/* RESET */
	html{color:#000;background:#FFF}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0}table{border-collapse:collapse;border-spacing:0}fieldset,img{border:0}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal}ol,ul{list-style:none}caption,th{text-align:left}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal}q:before,q:after{content:''}abbr,acronym{border:0;font-variant:normal}sup{vertical-align:text-top}sub{vertical-align:text-bottom}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;*font-size:100%}legend{color:#000}#yui3-css-stamp.cssreset{display:none}
	
	/* HTML/BODY */
	*, :after, :before {
	    -webkit-box-sizing: border-box;
	    box-sizing: border-box;
	}
	html {
	   	font-size: 62.5%;
	}
	body {
	    font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
		font-size: 1.45rem;
	}
	html,body {
		background: #f7f7f7;
	    color: #2b2b2b;
	    -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
	}

	/* LISTS */
	ul {
	    display: block;
	    list-style-type: disc;
	   	margin: 0 0;
	   	padding: 0 0 0 40px;
	}
	li {
	    display: list-item;
	}
	.dl-horizontal dt {
        float: left;
	    text-align: right;
	    overflow: hidden;
	    text-overflow: ellipsis;
	    white-space: nowrap;
	    width: 85px;
	    clear: left;
	    font-weight: 600;
	}
	.dl-horizontal dd {
	   	margin-left: 100px;
	}

	/* TYPOGRAPHY */
	p {
		margin: 1em 0;
		padding: 0;
	}
	pre, code {
        font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
	    line-height: 1.8;
	    font-size: 1.2rem;
	    -webkit-font-smoothing: initial;
	    -moz-osx-font-smoothing: initial;
	}
	code {
	    font-size: 1.4rem;
	    color: #717171;
	}
	p code {
		padding: .2rem .4rem;
	}
	a {
	    color: rgb(17, 85, 204);
	    text-decoration: none;
	}
	strong {
		font-weight: 600;
	}
	h1, h2, h3, h4, h5, h6 {
		color: #333;
		margin-bottom: 16px;
		font-weight: normal;
	}
	h1 {
	    font-size: 3rem;
	}
	h2 {
	    font-size: 2rem;
	}
	h3 {
	    font-size: 1.8rem;
	}
	h4 {
	    font-size: 1.6rem;
	}
	h5 {
	    font-size: 1.4rem;
	}
	h6 {
	    font-size: 1.3rem;
	}
	.uppercase {
		text-transform: uppercase;
	}

	/* BUTTON */
	button,
	.button {
	  background: rgb(66, 133, 244);
	  border: 0;
	  border-radius: 2px;
	  color: #fff;
	  cursor: pointer;
	  font-size: .875em;
	  margin: 0;
	  padding: 10px 24px;
	  transition: box-shadow 200ms cubic-bezier(0.4, 0, 0.2, 1);
	  font-weight: bold;
	  user-select: none;
	}
	button:active,
	.button:active {
	  background: rgb(50, 102, 213);
	  outline: 0;
	}
	button:hover,
	.button:hover {
	  box-shadow: 0 1px 3px rgba(0, 0, 0, .50);
	}

	/* LAYOUT */
	.row {
		width: 100%;
		display: block;
		padding-top: 15px;
		padding-bottom: 15px;
	}
	.row:after {
	  content: "";
	  display: table;
	  clear: both;
	}		
	.interstitial-wrapper {
		width: 100%;
		max-width: 680px;
	    padding-top: 100px;
	    margin: 0 auto;
	    margin-bottom: 90px;
	    overflow: hidden;
	    padding-left: 10px;
	    padding-right: 10px;
	}

	/* STYLES */
	.icon {
		background-repeat: no-repeat;
	    background-size: 100%;
	    height: 72px;
	    margin: 0 0 40px;
	    width: 72px;
	   	user-select: none;
	    display: inline-block;
	    position: relative;
		content: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJAAAACQBAMAAAAVaP+LAAAAGFBMVEUAAABTU1NNTU1TU1NPT09SUlJSUlJTU1O8B7DEAAAAB3RSTlMAoArVKvVgBuEdKgAAAJ1JREFUeF7t1TEOwyAMQNG0Q6/UE+RMXD9d/tC6womIFSL9P+MnAYOXeTIzMzMzMzMzaz8J9Ri6HoITmuHXhISE8nEh9yxDh55aCEUoTGbbQwjqHwIkRAEiIaG0+0AA9VBMaE89Rogeoww936MQrWdBr4GN/z0IAdQ6nQ/FIpRXDwHcA+JIJcQowQAlFUA0MfQpXLlVQfkzR4igS6ENjknm/wiaGhsAAAAASUVORK5CYII=);
	}
	.error-msg {
		font-size: 1.3rem;
	}
	.error-desc {
	    color: #646464;
	    font-size: 1.3rem;
	}

	
	/* CODE BLOCK */
	.code-block pre {
		white-space: normal;
		overflow-y: auto;
		border: 1px solid #e0e0e0;
	    border-radius: 3px;
	}
	.code-block pre code {
		display: block;
		color: #646464;
	}
	.code-block .line {
		position: relative;
		padding-left: 55px;
	}
	.code-block .lineno {
		border-right: 1px dotted #dcdcdc;
	    position: absolute;
	    left: 0;
	    top: 0;
	    padding: 0 10px 0 10px;
	    color: #c1c1c1;
	}
	.code-block .linecode {
		white-space: pre;
		padding-right: 20px;
	}
	.line.error {
		background-color: rgb(255,82,82);
		color: #fff;
		display: table;
	}
	.line.error .lineno {
		color: #fff;
		border-right: 1px dotted #e82222;
	}
	.trace-list
	{
		border: 1px solid #e1e0e1;
		border-radius: 1px;
		white-space: nowrap;
    	overflow-x: auto;
    	padding: 15px 40px;
	}
	.trace-list li
	{
		margin-bottom: 4px;
	}
</style>

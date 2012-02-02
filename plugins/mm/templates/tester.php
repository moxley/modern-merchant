<p>Code tester</p>
<form method="POST" action="?a=mm.tester" id="mmTesterForm">
    <textarea name="code" cols="80" rows="10"><?php ph($this->code) ?></textarea><br/>
    <input type="submit" value="Execute" class="fieldSubmit"/>
</form>
<pre id="testerOutput"><?php ph($this->execute_output) ?></pre>

<style type="text/css">
#testerOutput {
    background-color: #ffa;
    height:           300px;
    margin-top:       1em;
    overflow:         auto;
    padding:          3px;
    width:            800px;
}
#mmTesterForm .fieldSubmit { margin-top: 1em; }
</style>

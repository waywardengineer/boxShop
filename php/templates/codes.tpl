 <div>
         <h4>Your doorcode is {permCode}</h4>
        <input id="permCodeFormBtn" type="submit" onclick="toggleCodeForms('permCodeForm')" value="change" />
         <div id="permCodeForm" style="display:none;">
             <p>Codes must be at least 5 digits long. You can enter numbers or letters corresponding to a phone keypad.</p>

             <form action="proccode.php" method="post">
                <input type="hidden" name="doWhat" value="changePerm" />
                <div class="formRow">
                    {permCodeError}
                    <div style="position:absolute; top:5px; left:21px;">Code:</div>
                    <input id="permCode" name="permCode" maxlength="30" class="formfield" type="text" value="{permCode}" style="position:absolute; top:5px; left:65px;">
                    <input name="Button" type="button" class="submitbtn" style="position: absolute; top: -2px; left: 221px;" value="Make Random Code" onclick="getRandomCode('#permCode')">
        
                </div>
                
            	<div class="formRow">
                    <input value="Change Code" class="submitbtn" type="submit" style="position:absolute; top:5px; left:65px;">
				</div>
                
            </form>
        </div>
      
         
         
         <p style="font-size:1em;">You have access to: <strong>{accessZones}</strong></p>
         
 </div>
         

 
 
 
    <div>
        <h4>Temporary doorcodes for guests</h4>
         <input id="tempCodeFormBtn" type="submit" onclick="toggleCodeForms('tempCodeForm')" value="Add Temp Code" />
 
        <div id="tempCodeForm" style="display:none;">
             <p>Codes must be at least 5 digits long. You can enter numbers or letters corresponding to a phone keypad.</p>
             
            <form action="proccode.php" method="post">
            	<input type="hidden" name="doWhat" value="addTemp" />
            	{codeDateError}
            	<div class="formRow">
                    <div style="position:absolute; top:5px; left:25px;">Date:</div>
                    <input name="codeStartDate" id="codeStartDate" maxlength="30" class="formfield" type="text" value="{codeStartDate}" style="position:absolute; top:5px; left:65px;">
 
				</div> 
            	<div class="formRow">
                    <div style="position:absolute; top:5px; left:-10px;">Starting at:</div>
 
                  <select name="codeStartTime" id="codeStartTime" class="formfield" style="position:absolute; top:5px; left:65px;">
                    <option value="6">6AM</option>
                    <option value="10" selected="selected">10AM</option>
                    <option value="14">2PM</option>
                    <option value="18">6PM</option>
                    <option value="22">10PM</option>
                  </select>

                     <div style="position:absolute; top:5px; left:142px;">Lasting for:</div>
 
                  <select name="codeDuration" id="codeDuration" class="formfield" style="position:absolute; top:5px; left:218px;">
                    <option value="2">2 hours</option>
                    <option value="4" selected="selected">4 hours</option>
                    <option value="6">6 hours</option>
                    <option value="8">8 hours</option>
                    <option value="12">12 hours</option>
                    <option value="24">24 hours</option>
                    <option value="48">48 hours</option>
                    <option value="72">72 hours</option>

                  </select>
                </div>
                {codeCodeError}
            	<div class="formRow">
                    <div style="position:absolute; top:5px; left:21px;">Code:</div>
                    <input id="codeCode" name="codeCode" maxlength="30" class="formfield" type="text" value="{codeCode}" style="position:absolute; top:5px; left:65px;">
                    <input name="Button" type="button" class="submitbtn" style="position: absolute; top: -2px; left: 221px;" value="Make Random Code" onclick="getRandomCode('#codeCode')">

                </div>
            	<div class="formRow">
                    <div style="position:absolute; top:5px; left:20px;">Notes:</div>
                    <input name="codeNotes" maxlength="100" class="formfield" type="text" value="{codeNotes}" style="position:absolute; top:5px; left:65px;">
                </div>
            	<div class="formRow">
                    <input value="Add Temp Code" class="submitbtn" type="submit" style="position:absolute; top:5px; left:65px;">
				</div>
            </form>
            </div>
            <p style="font-size:1em; font-weight:bold;">Your existing temporary codes:</p>
        {codes}
    </div>
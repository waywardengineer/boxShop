{scripts}

	<script type="text/javascript">
        function changeLevel(uid, username){
            var formname = 'adminForm' + uid;
            var theform=document.forms[formname];
            var fieldname = 'action' + uid;
            theform.elements[fieldname].value = "level";
            fieldname = 'level' + uid;
            var thefield=theform.elements[fieldname].value; 
            var strings = [];
            strings[-1]="Really delete " + username + '?';
            strings[1]="Really demote " + username + ' to a "new user"?';
            strings[3]="Make " + username + ' a group?';

            strings[9]="Make " + username + " an admin?";
            if (thefield != 2) {
                var answer = confirm  (strings[thefield]);
            }
            else {
                var answer=1;
            }
            if (answer){
                theform.submit();
            }
        }

        function changeDoor(uid){
            var formname = 'adminForm' + uid;
            var theform=document.forms[formname];
            var fieldname = 'action' + uid;
            theform.elements[fieldname].value = "door";
            theform.submit();
        }


    </script>
{/scripts}

{content}

{users}



{/content}

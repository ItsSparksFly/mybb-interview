<h1>Interview im Profil</h1>
Über dieses Plugin könnt ihr im AdminCP Fragen hinterlegen, die eure User in ihrem UserCP beantworten können - ein alternative Form zum Standardsteckbrief zum Beispiel! Die Antworten auf diese Fragen werden anschließend im Profil ausgegeben - dabei könnt ihr als Admins entscheiden, ob alle Fragen oder eine zufällige Anzahl an Fragen (samt Antworten) im Profil ausgegeben werden. 

Die Seite kann über usercp.php?action=interview erreicht werden. Im MyBB-Standardtheme wird der UserCP-Navigation der Punkt "Interview" hinzugefügt, er ist für alle Usergruppen sichtbar. 

<h1>Neue Templates</h1>
<ul>
<li>interview_usercp
<li>interview_usercp_bit
<li>interview_usercp_nav
<li>interview_member_profile
</ul>

<h1>Variablen</h1>
Fügt <b>{$profile_answers}</b> dort ein, wo im Profil (member_profile-Template) die Fragen samt Antworten eines Users angezeigt werden sollen. Hier ist allerdings noch Arbeit nötig, um das Plugin an euer Design anzupassen. Die Anzeige ist sehr simpel und "MyBB Standard". 

<h1>Neue Datenbanktabellen</h1>
<ul>
<li>interview_questions
<li>interview_answers
</ul>

<center>
<img src="https://snipboard.io/WG6UzQ.jpg" />

<img src="https://snipboard.io/6fJgiN.jpg" />

<img src="https://snipboard.io/4t3v5E.jpg" /></center>

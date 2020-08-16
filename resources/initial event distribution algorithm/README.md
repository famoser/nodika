# Notfalldienst
a utility to distribute dates to multiple persons over a timespan following certain rules

Folgend die Erklärung, wie der Algorithmus funktioniert, der die Notfalldienstdaten auf die Praxen verteilt.
 1.	Einlesen aller Daten aus dem vergangen Jahr. Dazu gehört der Score der Praxen (mehr dazu später), eine Liste der generierten Termine und der Wochenenddienst, der den bereits generierten und den noch zu generierenden Zeitraum überlappt.
 2.	Es wird eine Liste aller Tage erstellt, die jeden Tag nach Typ unterscheidet. Es sind dies Feiertag, Sonntag, Samstag und Wochentag.
 3.	Es wird die benötigte Anzahl Punkte erstellt, und die Tage auf die Punkte verteilt. Es wird dabei darauf geachtet, dass jeder Punkt etwa den selben Score hat, und etwa gleich viel Typen von Tagen besitzt. Der Score wird nach Anzahl Tagen berechnet, wobei jeder Typ Tag mit einem bestimmten Wert multipliziert wird. So hat ein Feiertag den Wert 2, Sonntag 1.5, Samstag 1.2 und Wochentag 1. Je tiefer der Score eines Punktes, desto weniger und desto «harmlosere» Tage hat er. Es werden in diesem Schritt nur die Tage verteilt, jedoch noch keine konkreten Daten festgelegt!
 4.	Nun werden die Punkte auf die Praxen verteilt; dabei werden auf folgende Punkte geachtet:
   -	Der Score vom Vorjahr soll möglichst ausgeglichen werden
   -	Keine Praxis soll sofern möglich mehr als ein Feiertag bekommen
   -	Der Durchschnitt aller Punkte pro Praxis soll möglichst ausgeglichen sein.
 5.	Nun kann ein Score pro Praxis (Summe aller Scores der Punkte) berechnet werden. Der Unterschied vom Durchschnitt aller Scores wird für das nächste Jahr gespeichert.
 6.	Zuletzt werden die konkreten Daten den Praxen zugewiesen. Der Algorithmus basiert auf Zufall und optimiert den Abstand zwischen zwei Notfalldiensten einer Praxis. Er fängt an bei einem vorgegebenen Mindestabstand, und probiert einen Plan dafür zu erstellen. Geht es nicht auf, fängt er einfach wieder von vorne an. Er versucht das mehrere Tausend Mal, klappt dies dann immer noch nicht, wird der Mindestabstand verkürzt. Dies passiert so lange, bis eine Lösung gefunden wird. 

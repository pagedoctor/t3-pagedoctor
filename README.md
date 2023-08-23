# Willkommen bei Pagedoctor für das TYPO3 CMS

## Inhaltsverzeichnis

- [Was ist Pagedoctor?](#was-ist-pagedoctor)
- [Erweiterungen](#erweiterungen)
- [Einführung](#einfhrung)
- [Mitwirken](#mitwirken)
- [Lizenz](#lizenz)
- [Markenrichtlinie](#markenrichtlinie)

## Was ist Pagedoctor?

Pagedoctor ist ein Codegenerator zur einfachen Verwaltung und Bereitstellung von Inhaltselementen für das TYPO3 CMS.
Es macht es leichter Inhaltselemente rasch zu entwickeln und auf mehreren Systemen gleichzeitig bereitzustellen.

### Entwicklungsgeschwindigkeit vor Konfigurierbarkeit

Pagedoctor ist ein zentrales Instrument zur Verwaltung des Steuercodes von Inhaltselementen und nimmt dem Entwickler das Schreiben von Boilerplatecode ab.
Damit folgt es dem Prinzip der "Convention over Configuration". Einem Prinzip das besagt das man nur Dinge in der Softwareentwicklung definieren
sollte die unkonventionell sind. Offentsichtliche Dinge sollten ohne Programmieraufwand bereits funktionieren.
Ziel von Pagedoctor ist es, dem Entwickler ermüdende Aufgaben abzunehmen um effizienter Arbeiten zu können.

### Frisch beginnen

Neue Projekte können in Pagedoctor frisch begonnen werden. Die erkömmliche herangehensweise ist es in TYPO3 bestehende
Standard-Inhaltselemente von TYPO3 (Fluid Styled Contents) umzumodellieren. Wir sind der Ansicht das jedes Projekt
frisch beginnen sollte um Entwickler nicht in Ihrem kreativen Prozess zu beeinflussen. Zu diesem Zweck bietet Pagedoctor
einen starken Contentmodellierer, mit dem man innerhalb von wenigen Sekunden Inhaltselemente mit einer individuellen
Feldstruktur erstellen kann.

### Inkrementelle Quelltextstände

Bestehende Projekte müssen oft erweitert werden da neue Inhaltstypen hinzukommen. Anstelle also Codestände zu modifizieren und
so die Komplexität zu erhöhen, gibt es in Pagedoctor die Möglichkeit von Inhaltstypen sogenannte Artefakte zu erstellen.
Ein Artefakt ist eine Momentaufnahme der Inhaltstypen. So können bestehende Inhaltselemente und deren Vorlagen im Produktivbetrieb
bestehen bleiben während an einem neuen Stand gearbeitet wird. Ist die Programmierung der Vorlagen abgeschlossen wird einfach
das neuste Artefakt installiert und die neuen Inhaltstypen sind nutzbar.


### Keine Migrationen. Keine Probleme.

Anders als andere Tools benötigt Pagedoctor keine Datenbankmigrationen. Dies erleichert Entwicklern die Fortentwicklung
und Migrationen auf Produktivsystemen da das Datenbankschema nicht verändert werden muss. Ein springen zwischen
verschiedenen Quelltextständen ist so auch problemlos möglich. 

## Erweiterungen

Pagedoctor kommt mit dieser kostenfreien Erweiterung.

Darüber hinaus kommt Pagedoctor mit:

- [Pagedoctor Starter](https://github.com/pagedoctor/t3-starter), eine Provider Erweiterung zum einfachen starten von neuen TYPO3 CMS Projekten

## Einführung

Gehen Sie auf die [offizielle Dokumentation](https://www.pagedoctor.de/installation) um alle Installationsschritte einzusehen.

## Mitwirken

Wir freuen uns über Beisteuerung von Bugfixes oder Anregungen. Nutzen Sie bitte dafür den Bereich [Issues](https://github.com/pagedoctor/t3-pagedoctor/issues).

## Lizenz

Die Pagedoctor Erweiterung wird unter der [GPL2 Lizenz](LICENSE) veröffentlicht.

## Markenrichtlinie

Bitte lesen Sie unsere [Markenrichtlinie](https://www.pagedoctor.de/trademark-policy) um fälschliche Namensverwendung zu vermeiden.

Copyright 2023, Pagedoctor ist eine Marke von Colin Atkins.
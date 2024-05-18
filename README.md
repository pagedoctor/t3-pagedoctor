# Welcome to Pagedoctor

## What's Pagedoctor?

The emerge of headless content management systems (CMS) brought independence
in terms of freely choosing a programming language for the output of contents.
Headless CMS decouple content management and hosting from output and business logic.
It does so by providing tools to access a remotely hosted content management system
to which access is given by an API.

This trend however has its downsides as it brings great dependence on the providers
platform in many ways. Firstly you are completely dependend on the infrastructure
as all your content is stored on their servers. Secondly you gate yourself into
their platform making it difficult to escape from once you integrated your website.
Thirdly is cost. The current hikes in cloud computing and the centralization of
content storage blows up the monthly fees which are not feasable for businesses
which need to work cost effectively.

On the other hand, regular CMS like WordPress are the opposite. With them
you are completely hosting your own content. Secondly, as on-premise CMS are mostly
OpenSource they offer a great deal of flexibility. Thirdly, their cost is still
low as they're usually don't require a premium fee every month as they're hosted
on cheap web hosting providers.

Pagedoctor aims to tackle the disadvantages of headless CMS while having the
advantages of regular CMS. It does so by providing a way to structure your contents
into different types which are distributed to multiple systems with an integrated
release management and deployment feature.

Pagedoctor functions as a Content Structure Proxy by equipping websites with
a platform independent tool for designing, packing and deploying the needed
content structure which otherwise had to be programmed by hand.
All while the actual content and code for using that structure is stored and
managed on-premise with a regular hosting provider.

It does so with an integrated Code generator which generates all needed content
type structural data. All of this without the need of database SQL migrations
as Pagedoctor cleverly maps existing database fields for all common types like
texts, images, numbers, relations and embedded contents to any MySQL or 
PostgreSQL database.















Pagedoctor aims to close the gap of the ability to choose a consuming framework
freely while not beeing dependent on what CMS you use. It does so by providing
a Content Structure Proxy with adjacent tools. Those tools are programming language
specific, that means they work for PHP (more in progress)
Pagedoctor aims to decouple content management from content output also, but
it achieves that by providing only an Data Structure Proxy with adjascent
tools. This means that you design your Data Structures with Pagedoctor, and
deploy that structure information to your

Pagedoctor aims to tackle this issue by decouple content management from content output.
It sticks to the well known paradigm of monolithic server-client applications.




However, it simplifies 
underlying self hosted content management systems. It does so by providing
a Data Structure Proxy with adjascent features like code generation and
release deployments. This enables any supported programming language or
framework to communicate with an backend content management system.

This decouples content management from content output. It enables you as a
developer to use whatever framework you want to implement the business logic.
All while keeping the way how content is stored and accessed from the database.

Pagedoctor is a Data Structure Proxy with an integrated Code generator.
Imagine 
It is an easy to use service with adjascent tools to structure data and output that
data on various . Design your projects data structure through types.
Deploy a new release with one click Once you designed the projects data structure
and their individual types for your latest content management website you simple deploy the 
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

Die Pagedoctor Erweiterung wird unter der [MIT Lizenz](MIT-LICENSE) veröffentlicht.

## Markenrichtlinie

Bitte lesen Sie unsere [Markenrichtlinie](https://www.pagedoctor.de/trademark-policy) um fälschliche Namensverwendung zu vermeiden.

Copyright 2024, Pagedoctor ist eine Marke von Colin Atkins.


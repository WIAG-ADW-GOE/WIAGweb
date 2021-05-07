{% set wiagbaseurl='https://' ~ app.request.getHttpHost() %}
WIAG API
========

Das Application Programming Interface (API) für WIAG ermöglicht die automatisierte
Abfrage von Daten aus dem WIAG Datenbestand:
- [Bischöfe](#bischoefe)
  - [Einzelabfrage](#bischofeeinzel)
  - [Suchanfrage](#bischoefesuche)
- [Bistümer](#bistuemer)
  - [Einzelabfrage](#bistumeinzel)
  - [Liste](#bistuemerliste)

Die Daten werden als ein [JSON](https://www.json.org/json-de.html)- oder CSV-Dokument ausgeliefert.

## <a id="bischoefe"></a>Bischöfe

### <a id="bischofeinzel"></a>Einzelabfrage
Mit der Angabe einer WIAG-Kennung erhält man alle Elemente eines Datensatzes. Die URL hat folgenden Aufbau: `{{ wiagbaseurl }}/id/[ID]?format=[json|csv]`. 

Beispiel:<br/>
{{ url('id', {id: 'WIAG-Pers-EPISCGatz-10076-001', format: 'json'})|replace({'http:' : 'https:'}) }}<br/>
{{ url('id', {id: 'WIAG-Pers-EPISCGatz-10076-001', format: 'csv'})|replace({'http:' : 'https:'}) }}

#### Struktur
Das JSON-Dokument enthält ein Element, das die einzelnen Angaben zu der
Person umfasst. Dazu gehört bei fast allen Personen eine Gruppe von externen Kennungen im Element `identifiers` sowie eine Liste von Ämtern im Element `offices`.

Beispiel:
``` json
 {"wiagId":"WIAG-Pers-EPISCGatz-10076-001",
  "familyName":"Braida",
  "givenName":"Franz Julian",
  "prefix":"Graf von",
  "comment_person":"Ep. tit. Hipponensis",
  "dateOfBirth":"1654",
  "dateOfDeath":"1727",
  "identifier":
  {"viafId":"5652149719115111130002",
   "wikidataId":"Q12017135"},
  "offices":[
	  {"officeTitle":"Weihbischof",
	   "diocese":"Olmütz",
	   "dateStart":"1703",
	   "dateEnd":"1727"},
	  {"officeTitle":"Generalvikar",
	   "diocese":"Olmütz",
	   "dateStart":"1703",
	   "dateEnd":"1727"}],
  "reference":
  {"title":"Die Bischöfe des Heiligen Römischen Reiches 1648 bis 1803",
   "author":"Gatz, Erwin",
   "short":"Gatz, Bischöfe 1648 bis 1803",
   "pages":"41"
  }
 }
```

Das CSV Dokument ist ein UTF-8-Text. Die erste Zeile enthält die Feldbezeichner. Die
folgende Zeile enthält die Feldwerte. Die Feldinhalte einer Zeile sind durch
Tabulator voneinander getrennt.

Beispiel:
``` text
wiagId	familyName	givenName	prefix	commentPerson	dateOfBirth	dateOfDeath	identifier.viafId ...
WIAG-Pers-EPISCGatz-10076-001	Braida	"Franz Julian"	"Graf von"	"Ep. tit. Hipponensis"	1654	1727	5652149719115111130002 ...

```

<a id="csvinbrowser"></a>Die meisten Browser zeigen einen Auswahldialog, bei dem entschieden werden kann, ob
die Daten in einer Datei gespeichert oder direkt angezeigt werden sollen. Hinweis für
Microsoft-Windows Benutzer: Die Anwendung *Editor* zeigt die Daten korrekt an. Die
Anwendung *Excel* geht meistens von einer anderen Kodierung als UTF-8 aus und
erwartet ein Komma statt des Tabulators als Trennzeichen. Daher ist die Anzeige
direkt in *Excel* meistens nicht sinnvoll. Die Daten können aber aus einer Datei
heraus über den Importdialog korrekt eingelesen werden, indem die Auswahl für
*Dateiursprung* und *Trennzeichen* entsprechend eingestellt werden.

### <a id="bischoefesuche"></a>Suchanfrage
Mit der Angabe von Suchparametern erhält man alle Datensätze, die der Suchanfrage
entsprechen. Gesucht werden kann nach folgenden Eigenschaften:

- **name**: Finde Übereinstimmugen in Vorname, Nachname, Namenspräfix, Varianten des
  Vornames und Varianten des Nachnamens.
  Beispiele: `josef`, `graf`,
  `gondo`, `Franz Josef Graf von Gondola`
- **diocese**: Finde Übereinstimmugen in den Name der Bistümer, in denen die Person ein
  Amt innehatte.
  Beispiele: `burg`, `würzburg`.
- **office**: Finde Übereinstimmungen in den Amtsbezeichnungen.
  Beispiele: `vikar`,
  `administrator`.
- **year**: Finde Übereinstimmungen für einen Zeitraum von plus/minus 50 Jahren zu der
  angegebenen Jahreszahl. Berücksichtigt wird für die einzelne Person der größte
  Zeitraum, der sich ergibt aus Geburtsdatum, Sterbedaten, Amtsbeginn und Amtsende.
- **someid**: Finde eine exakte Übereinstimmungen mit einer Kennung für eine Person in
  folgenden Verzeichnissen:
  - [WIAG]({{ url('wiag_welcome') }})
  - [Gemeinsame Normdatei (GND)]({{ urlgnd }})
  - [Virtual International Authority File (VIAF)]({{ urlviaf }})
  - [Wikidata]({{ urlwikidata }})
  - [Personendatenbank der Germania Sacra]({{ urlgspersons }})

Die Suchparameter sind logisch UND-verknüpft: Es werden nur solche Datensätze angezeigt, für die alle Parameter/Wert-Kombinationen zutreffen.
Die Suchparameter werden an die URL jeweils mit dem Schlüsselwort angehängt. Ebenso
wird das gewünschte Format mit dem Schlüsselwort `format` angehängt. JSON ist das
Standard-Format, d.h. hier kann die Angabe des Formats entfallen:
`{{ wiagbaseurl }}/api/query-bishops?key1=value1&key2=value2&format=[json|csv]`

Beispiele (JSON):<br/>
{{ url('api\_query\_bishops', {name: 'gondo', format: 'json'})|replace({'http:' : 'https:'})|raw }}<br/>
{{ url('api\_query\_bishops', {name: 'Hohenlohe', diocese: 'Bamberg'})|replace({'http:' : 'https:'})|raw }}<br/>
{{ url('api\_query\_bishops', {diocese: 'Trier', year: '1450', format: 'json'})|replace({'http:' : 'https:'})|raw }}<br/>
{{ url('api\_query\_bishops', {someid: 'WIAG-Pers-EPISCGatz-3302-001', format: 'json'})|replace({'http:' : 'https:'})|raw }}<br/>
{{ url('api\_query\_bishops', {someid: 'Q1506604'})|replace({'http:' : 'https:'})|raw }}<br/>

Beispiele (CSV):<br/>
{{ url('api\_query\_bishops', {name: 'gondo', format: 'csv'})|replace({'http:' : 'https:'})|raw }}<br/>
{{ url('api\_query\_bishops', {name: 'Hohenlohe', diocese: 'Bamberg', format: 'csv'})|replace({'http:' : 'https:'})|raw }}<br/>
{{ url('api\_query\_bishops', {diocese: 'Trier', year: '1450', format: 'csv'})|replace({'http:' : 'https:'})|raw }}<br/>
{{ url('api\_query\_bishops', {someid: 'WIAG-Pers-EPISCGatz-3302-001', format: 'csv'})|replace({'http:' : 'https:'})|raw }}<br/>
{{ url('api\_query\_bishops', {someid: 'Q1506604', format: 'csv'})|replace({'http:' : 'https:'})|raw }}<br/>

Siehe [Hinweise zur Anzeige im Browser](#csvinbrowser).

#### Struktur
Das JSON-Dokument enthält ein Element `persons`, mit der Liste der Datensätze.

Beispiel:
```json
{
  "persons": [
    {
      "wiagId": "WIAG-Pers-EPISCGatz-03302-001",
      "familyName": "Hohenlohe",
      "givenName": "Georg",
      "prefix": "von",
      "dateOfBirth": "um 1350",
      "dateOfDeath": "1423",
      "identifier": {
        "gsId": "019-01009-001",
        "gndId": "124115535",
        "viafId": "15696513",
        "wikidataId": "Q1506604",
        "wikipediaUrl": "https://de.wikipedia.org/wiki/Georg_von_Hohenlohe"
      },
      "offices": [
        {
          "officeTitle": "Bischof",
          "diocese": "Passau",
          "dateStart": "1389",
          "dateEnd": "1423",
          "sort": 6000
        },
		...
      ],
    },
	{
      "wiagId": "WIAG-Pers-EPISCGatz-02554-001",
      "familyName": "Hohenlohe",
      "givenName": "Friedrich",
      "prefix": "von",
      "dateOfDeath": "1352",
      "identifier": {
        "gsId": "054-00923-001",
        "gndId": "110092236",
        "viafId": "37502849",
        "wikidataId": "Q1459890",
        "wikipediaUrl": "https://de.wikipedia.org/wiki/Friedrich_I._von_Hohenlohe"
      },
	  ...
    }
	...
  ]
}
```

Das CSV Dokument ist ein UTF-8-Text. Die erste Zeile enthält die Feldbezeichner. Die
folgenden Zeilen enthalten die Feldwerte. Die Feldinhalte einer Zeile sind durch Tabulator voneinander getrennt.

Beispiel:
```text
wiagId	familyName	givenName	prefix	commentPerson	dateOfBirth	dateOfDeath	identifier.gsId	identifier.gndId	...
WIAG-Pers-EPISCGatz-03302-001	Hohenlohe	Georg	von		"um 1350"	1423	019-01009-001	124115535	...
WIAG-Pers-EPISCGatz-02554-001	Hohenlohe	Friedrich	von			1352	054-00923-001	110092236	...
WIAG-Pers-EPISCGatz-12609-001	Hohenlohe-Waldenburg-Bartenstein	"Joseph Christian Franz"	"Prinz zu"		1740	1817	048-03097-001	119536463	...
WIAG-Pers-EPISCGatz-03753-001	Hohenlohe	Gottfried	von			1322	059-00674-001	100943365	...
WIAG-Pers-EPISCGatz-03757-001	Hohenlohe	Albrecht	von			1372	059-00048-001	11864775X	...
WIAG-Pers-EPISCGatz-12605-001	Hohenlohe-Waldenburg-Schillingsfürst	"Franz Karl Joseph"	"Fürst von"	"1812–1819 Generalvikar von Ellwangen. Titularbistum Tempe"	1745	1819		1169559   ...
```

Siehe [Hinweise zur Anzeige im Browser](#csvinbrowser).

## <a id="bistuemer"></a>Bistümer

### <a id="bistumeinzel"></a>Einzelabfrage
Mit der Angabe einer WIAG-Kennung erhält man alle Elemente eines Datensatzes. Die URL hat folgenden Aufbau: `{{ wiagbaseurl }}/api/diocese/[ID]?format=[json|csv]`. 

Beispiel:<br/>
{{ url('api\_diocese', {wiagidlong: 'WIAG-Inst-DIOCGatz-047-001', format: 'json'}) }}<br/>
{{ url('api\_diocese', {wiagidlong: 'WIAG-Inst-DIOCGatz-047-001', format: 'csv'}) }}

#### Struktur
Das JSON-Dokument enthält ein Element `diocese`, das die einzelnen Angaben zu dem
Bistum umfasst. Dazu gehört bei fast allen Bistümern eine Gruppe von externen Kennungen im Element `identifiers` sowie eine Liste von alternativen Bezeichnungen in unterschiedlichen Sprachen im Element `altLabels`.

Beispiel:
``` json
{
  "diocese": {
    "wiagid": "WIAG-Dioc-47-001",
    "name": "Basel",
    "status": "Bistum",
    "dateOfFounding": "4. Jahrhundert",
    "dateOfDissolution": "1803",
    "altLabels": [
      {
        "altName": {
          "name": "ecclesia Basileensis",
          "lang": "la"
        }
      },
      {
        "altName": {
          "name": "Bâle",
          "lang": "fr"
        }
      },
      {
        "altName": {
          "name": "Basilea"
        }
      }
    ],
    "note": "Erste Erwähnungen eines Bischofs in Kaiseraugst bei Basel gehen auf 343/346 zurück. Die Kontinuität zum späteren Bistum Basel bleibt jedoch offen. Zur eigentlichen Christianisierung kam es erst im 7. Jahrhundert",
    "ecclesiasticalProvince": "Besançon",
    "bishopricSeat": "Basel",
    "noteBishopricSeat": "Im Zuge der Reformation wurden Bischof und Domkapitel aus Basel vertrieben. Die Bischöfe residierten seit 1527 in Pruntrut (Porrentruy), das Domkapitel in Freiburg im Breisgau, ab 1678 in Arlesheim.",
    "identifiers": {
      "Factgrid": "Q153251",
      "Gemeinsame Normdatei (GND) ID": "2029618-6",
      "Wikipedia-Artikel": "Bistum Basel",
      "Wikidata": "Q182492",
      "VIAF-ID": "131932928",
      "Catholic Hierarchy, Diocese": "dbase.html"
    },
    "identifiersComment": "Alle Normdaten nehmen sowohl auf das Fürstbistum als auch auf das heutige Bistum Basel Bezug."
  }
}
```

### <a id="bistuemerliste"></a>Listenabfrage
Die URL für die Abfrage einer Liste von Bistümern lautet: `{{ wiagbaseurl }}/api/query-dioceses?format=[json|csv]`. Optional kann nach dem Namen des Bistums gesucht werden durch den Parameter `name`: `{{ wiagbaseurl }}/api/query-dioceses?name=[name]&format=[json|csv]`.

Beispiel:<br/>
{{ url('api\_query\_dioceses', {format: 'json'})|replace({'http:' : 'https:'}) }}<br/>
{{ url('api\_query\_dioceses', {format: 'csv'})|replace({'http:' : 'https:'}) }}<br/>
{{ url('api\_query\_dioceses', {name: 'burg', format: 'json'})|replace({'http:' : 'https:'})|raw }}

#### Struktur
Das JSON-Dokument enthält ein Element `dioeses`, mit den Kindern `count` (Anzahl der Datensätze) und `list` (Liste der Datensätze).

Beispiel:
```json
{
  "dioceses": {
    "count": 5,
    "list": [
      {
        "diocese": {
          "wiagid": "WIAG-Dioc-30-001",
          "name": "Trier",
          "status": "Erzbistum",
          "dateOfFounding": "3. Jahrhundert",
          "dateOfDissolution": "1803",
		  ...
        }
	  }	
	...
	]
  }
}
```

# Glossary Component

A PHP Component for the Creation of Glossaries in HTML, LaTeX, Markdown, etc.


## Glossary Programming Language

### Main Definitions

The language is as follows:

```
Key-Name: #Some #Tags #And #Some !relative/path/image1.png !relative/path/image2.png
  Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor 
  invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam 
  et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est 
  Lorem ipsum dolor sit amet.
```

So the body of a definition is indented with a Tab ("\t").

### References

To make references, use an arrow symbol `=>` and write the referenced key afterwards. You can use squared brackets (`[...]`) to add some displayed text and curly brackets (`{...}`) to add text to referenced Key (won't be displayed).

### Empty Definitions

Just leave out the body of a definition (you can still use tags and images, though).

```
Empty Key: - #Some #Tags !empty !image
```

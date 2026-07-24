<!-- Page builder fixture: nested blocks, literals, lists, and inline comments -->

# name
About <!-- page title -->

# id
123

# path
/about

<!--
Main layout: primary content row plus a sidebar widget area.
-->

# content blocks
## 0
### width
8

### type
parent block <!-- row layout -->

### child blocks
#### 0
##### width
4

##### type
card

##### icon
star.svg

##### title
Yay <!-- first card -->

##### text
Lorem Ipsum... <!-- excerpt -->
Dolor sit amet...

##### link
###### url
```
#
```

###### text
Learn more

#### 1
##### width
4

##### type
card

##### icon
thumbsup.svg

##### title
Good

##### text
Lorem Ipsum...
Dolor sit amet...

##### link
###### url
https://example.com <!-- external -->

###### text
Learn more

#### 2
##### width
4

##### type
card

##### icon
check.svg

##### title
Done

##### text
Lorem Ipsum...
Dolor sit amet...

##### link
###### url
```
#
```

###### text
Learn more

#### 3
##### width
12

##### type
wysiwyg

##### content
Lorem ipsum dolor <!-- inline in multiline scalar -->
Sit amet conspectitur

#### 4
##### width
12

##### type
blog preview

##### max articles to show
3

##### categories
- news <!-- primary -->
- events
- spotlight

## 1
### width
4 <!-- sidebar column -->

### type
widget area

### widget area
right_sidebar

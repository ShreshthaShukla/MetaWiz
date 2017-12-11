# MetaWiz
Project of INF 385T-Metadata Generation and Interfaces for Massive Datasets

Synopsis:
The application extracts the data from Harry Potter movie transcripts available on a website and parses it. 
It then stores the parsed data in a database in a structured  format.
The user can then ask specific questions about the Harry Potter movies which will be answered by extracting the data from the database.

Prerequisites:
MongoDb server, Python editor

File Description:
The code includes a parser (Parser.py) that parses through the data in the transcripts and inserts the data into the MongoDb. 
We have changed the granularity of the data from a movie to a scene. This helps us in structuring the data and extracting it easily from the database.
the file config.py contains all the static variables that are used in the Parser.py.
To run the python script, please use the command with the title of the movie and the series number.
E.g.: python3 Parser.py --title "Harry Potter and the Philosopher's Stone" --seriesNumber "1"

Coding Style Guide:
The coding style used for the Python code is PEP 8 -- Style Guide for Python Code

Contributors:
The authors of this project are Avid Narimani, Prachi Singh, Shreshtha Shukla, and Nimish Kate

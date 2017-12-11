from bs4 import BeautifulSoup
import requests
import argparse
import sys
from pymongo import MongoClient

import config

url = "http://transcripts.wikia.com/wiki/Harry_Potter_and_the_Philosopher%27s_Stone"
request_url  = requests.get(url)
data = request_url.text
soup = BeautifulSoup(data, "lxml")

#Find all the HTML tags and trim them
paragraphs = str(soup.find_all(['p', '/p', 'i', '/i', '\n', 'b', '/b']))
paragraphs = paragraphs.split('<p>')

#Handle the bold tag in the transcript with the name of the movie
if '<i>' in paragraphs:
    paragraphs = paragraphs.split('</i>')[1]
    if '<b>' in paragraphs:
        paragraphs = paragraphs.split('</b>')[1]
#Handle the /n in the transcript        
elif '/n' in paragraphs:
    paragraphs = paragraphs.split('/n')

    
scene_sequence = 0  #Initialize scene_sequence and sequence variables
sequence = 0
scene = ''  #Reset variables to null for every occurence in the loop


#MongoDB connection code starts 
mongoConn = MongoClient('mongodb://'+ config.DATABASE_CONFIG['user'] + ':' + config.DATABASE_CONFIG['password'] + '@' + config.DATABASE_CONFIG['host'] + ':' + str(config.DATABASE_CONFIG['port']) + '/' + config.DATABASE_CONFIG['dbname'])

#Store the Database Name and the Collection Name in variables
database = mongoConn[config.DATABASE_CONFIG['dbname']]
config_collectionName = config.DATABASE_CONFIG['collectionname']

#Get the Title of the Movie and the Series Number from Command Line 
title = '' 
seriesNumber= ''

if("--title" in  sys.argv and "--seriesNumber" in  sys.argv):
    title = sys.argv[sys.argv.index("--title") + 1]
    seriesNumber = sys.argv[sys.argv.index("--seriesNumber") + 1]

paragraph_Index = 1 #Initialize the variable that stores the index
while paragraph_Index < len(paragraphs):
    if 'Scene:' in paragraphs[paragraph_Index]: #If "Scene:" is found, store that paragraphs
        scene = paragraphs[paragraph_Index].split(':')[1] #Split the scene found in the transcript such that only the text after the word 'Scene:' is stored
        scene = scene.split('<')[0] 
        paragraph_Index = paragraph_Index +1 #Increment the index by one on each turn of the loop
        scene_sequence = scene_sequence + 1  #Increment the Sequence of the scene by one on each turn of the loop
        sequence = 0
    elif '(Scene:' in paragraphs[paragraph_Index]: #Split the scene found in the transcript such that only the text after the word '(Scene:' is stored
        scene = paragraphs[paragraph_Index].split(':')[1]
        scene = scene.split('<')[0] 
        paragraph_Index = paragraph_Index + 1
        scene_sequence = scene_sequence + 1
        sequence = 0
    elif '<i>' in paragraphs[paragraph_Index]:
        buffer_desc = paragraphs[paragraph_Index].split('<i>')[1]
        italicsTrim = buffer_desc.split('</i>')[0]
        if '<b>' in italicsTrim:
            scene = italicsTrim.split('<b>')[1]
        paragraph_Index = paragraph_Index + 1
        scene_sequence = scene_sequence + 1
        sequence = 0
		
    #Array of contents to store the descriptions and sequence
    contentsList = []        
    sequence = sequence + 1 #Increment the sequence by 1
    content={
            'sequence':sequence,
            'description':scene
            }
            
    print('---------' + 'title' + '---------')
    print(title)
    print('---------' + 'seriesNumber' + '---------')
    print(seriesNumber)
    print('---------' + 'scene' + '---------')
    print(scene)
    print('---------' + 'scene sequence' + '---------')
    print(scene_sequence)
    
    contentsList.append(content)
    while paragraph_Index < len(paragraphs):
        character = ''
        description = ''
        text = ''
        if 'Scene:' in paragraphs[paragraph_Index]: #If "Scene:" is found, terminate the current loop and resume execution at the next statement
            break
        elif '(Scene:' in paragraphs[paragraph_Index]: #If "(Scene:" is found, terminate the current loop and resume execution at the next statement
            break
        elif '<b>' in paragraphs[paragraph_Index]  and '</b>' in paragraphs[paragraph_Index]: #If "<b>" or "</b>" is found, terminate the current loop and resume
            break                                                                             #execution at the next statement
        elif '(' in paragraphs[paragraph_Index] and ')' in paragraphs[paragraph_Index]:
            buffer_description = paragraphs[paragraph_Index].split('(')[1] #Split the description such that only the text between "(" and ")" is stored
            description = buffer_description.split(')')[0]
            if '<i>' in description:
                buffer_description = buffer_description.split('<i>')[1] #Split the description such that only the text between "<i>" and "</i>" is stored
                description = buffer_description.split('</i>')[0]
            sequence = sequence + 1
        elif ':' in paragraphs[paragraph_Index]:
            text = paragraphs[paragraph_Index].split(':')[1]
            text = text.split('<')[0] #Split the data in the transcript to separate the text spoken by the character
            character = paragraphs[paragraph_Index].split(':')[0]
            character = character.split('(')[0] #Split the data in the transcript to separate the name of the character
            sequence = sequence + 1
        elif '{' in paragraphs[paragraph_Index] and '}' in paragraphs[paragraph_Index]:
            description = paragraphs[paragraph_Index] #Store the data in description
            sequence = sequence + 1
        
        
        paragraph_Index = paragraph_Index +1
        #If a character name is encountered then insert the character name and the text spoken by him/her in the content array
        if(character != ''):
            content = {
                'sequence':sequence,
                'character':character,
                'text':text
                }
        #If the above condition is not satisfied, store the description encountered in the transcript in the content array
        else:
            content = {
                'sequence':sequence,
                'description':description
                }
        contentsList.append(content)
        print('---------' + 'sequence' + '---------')
        print(sequence)
        print('---------' + 'description' + '---------')
        print(description)
        print('---------' + 'text' + '---------')
        print(text)
        print('---------' + 'character' + '---------')
        print(character)
        
    new_posts = [{
    'movie' :{
            'title':title,
            'seriesNumber':seriesNumber
            },
    'sequence':scene_sequence,
    'contents' :contentsList
    }]
    
    #Insert the data into the MongoDb in the proper structure
    database[config_collectionName].insert(new_posts)
    

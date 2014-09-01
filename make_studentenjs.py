import glob, os.path

# now you can call it directly with basename

studenten = []
for file in glob.glob('pasfotos/*.JPG'):
    filename = os.path.basename(file)
    parts = os.path.splitext(filename)[0].split('_')

    parts[-1], parts[0] = parts[0], parts[-1]
    naam = ' '.join(parts)

    studenten.append({'naam': naam, 'foto': file})

print 'var studenten = ', studenten, ';'
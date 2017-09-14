import glob, os.path, cv2, time, numpy as np, math, json, csv
from unidecode import unidecode

year = 2017
studenten_groepen = list(csv.reader(open('studenten%s.csv' % year), delimiter=';'))

studenten = []
for file in glob.glob('studenten%s/*' % year)[:]:
    image = cv2.imread(file)
    white = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    white = cv2.GaussianBlur(white, (5, 5), 0)
    thres, white = cv2.threshold(white, 0, 255, cv2.THRESH_BINARY+cv2.THRESH_OTSU)

    #kernel =
    #white = cv2.morphologyEx(white, cv2.MORPH_OPEN, kernel)
    white = cv2.dilate(white, np.ones((5, 5),np.uint8))
    white = cv2.erode(white, np.ones((10, 10),np.uint8))

    white_copy = white.copy()

    something, contours, hierarchy = cv2.findContours(white_copy, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)

    squares = []
    for contour in contours:
        approx = cv2.approxPolyDP(contour, cv2.arcLength(contour, True)*0.09, True)

        area_percent = math.fabs(cv2.contourArea(approx) / (image.shape[0] * image.shape[1]))

        center_x = cv2.minEnclosingCircle(approx)[0][0]
        center_x_percent = center_x / image.shape[1]

        if len(approx) == 4 \
            and 0.01 < area_percent < 0.2 \
            and 0.2 < center_x_percent < 0.8 \
            and cv2.isContourConvex(approx):
            squares.append(approx)

    filename = os.path.basename(file)
    parts = os.path.splitext(filename)[0].split('_')

    parts[-1], parts[0] = parts[0], parts[-1]
    naam = ' '.join(parts)

    # Zoek een groep
    groep = ''
    for student in studenten_groepen:
        csv_voornaam = unidecode(student[3].decode('utf-8'))
        csv_achternaam = unidecode(student[1].decode('utf-8'))
        if(csv_voornaam == parts[0] and csv_achternaam.startswith(parts[-1])):
            groep = student[4]
    print naam
    if len(squares) > 0:
        vierkant = []
        for point in squares[0]:
            vierkant.append(list(point[0]))
    else:
        if False:
            canvas = cv2.cvtColor(white, cv2.COLOR_GRAY2BGR)
            cv2.drawContours(canvas, contours, -1, (0, 0, 255), 3)
            cv2.imshow('Input', canvas)
            if cv2.waitKey() == 27:
                quit(0)

        width, height = image.shape[1], image.shape[0]
        half_height = height * 0.5
        vierkant = [[0, half_height], [width, half_height], [width, height], [0, height]]

    studenten.append({'naam': naam, 'foto': file, 'vierkant': vierkant, 'grootte': [image.shape[1], image.shape[0]], 'groep': groep})

file = 'var studenten = ' + str(studenten) + ';'

f = open('studenten%s.js' % year, 'w')
f.write(file)
f.close()

print file
var file = window.location.hash.substring(1);
if(!file) {
  file = 'studenten2014';
}
console.log(file + '.js');
$.getScript(file + '.js', function() {
  var student;

  $('#naam').autocomplete({
      source: function(request, response) {
        var result = [];
        for(id in studenten) {
          if(studenten[id].naam.toLowerCase().indexOf(request.term.toLowerCase()) == 0) {
            result.push(studenten[id].naam);
          }
        }
        response(result);
      },
      delay: 0,
      select: function( event, ui ) {
        $('#verberger').fadeOut({complete: function() {
          var feedback;
          if(ui.item.value == student.naam) {
            feedback = $('#correct')
          } else {
            feedback = $('#helaas');
          }
          feedback.fadeIn().fadeOut();
        }}).delay(1000).fadeIn({complete: function() {
          nieuwenaam();
          $('#naam').val('');
        }});

        return true;
      }
    });

  function nieuwenaam() {
    student = studenten[Math.floor(Math.random() * studenten.length)];

    $('#foto img').attr('src', student.foto)

    var points = '';
    for(i in student.vierkant) {
      points += student.vierkant[i][0] + ',' + student.vierkant[i][1] + ' ';
    }

    $('svg')[0].setAttribute('viewBox', '0 0 ' + student.grootte[0] + ' ' + student.grootte[1]);
    $('#vierkant').attr('points', points);
  }

  nieuwenaam();

  $('#naam').keypress(function(e) {
    if(e.which == 13) {
      $('#verberger').fadeOut().delay(1300).fadeIn({complete: function() {
        nieuwenaam();
      }});
    }
  })

});
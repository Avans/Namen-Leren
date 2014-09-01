$(function() {
  var student;
  var testing = false;

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
        }}).delay(2000).fadeIn({complete: function() {
          nieuwenaam();
          $('#naam').val('');
        }});

        return true;
      }
    });

  function nieuwenaam() {
    student = studenten[Math.floor(Math.random() * studenten.length)];

    $('#foto img').attr('src', student.foto)
  }

  nieuwenaam();

  $('#naam').keypress(function(e) {
    if(e.which == 13) {
      $('#verberger').fadeOut().delay(2000).fadeIn({complete: function() {
        nieuwenaam();
      }});
    }
  })

});
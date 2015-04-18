var file = window.location.hash.substring(1);
if(!file) {
  file = 'studenten2014';
}

$.getScript(file + '.js', function() {
  var groepen = {};
  var selected_studenten = [];
  var raad_index = 0;

  for(i in studenten) {
    var groep = studenten[i].groep;
    if(groep.trim() == "")
      groep = "Onbekend";

    if(!groepen[groep])
      groepen[groep] = [];

    groepen[groep].push(studenten[i]);

  }
  console.log(groepen);
  groepen_sorted = Object.keys(groepen).sort();

  for(i in groepen_sorted) {
    var groep = groepen_sorted[i];
    $('#groepen').append(groep + ' <input type="checkbox" id="'+groep+'" class="groep" checked="checked"><br />');
    $('#' + groep).change(function() {
      reselect_studenten();
    });
  };

  $('#all').change(function() {
    $('.groep').prop('checked', $(this).prop('checked'));
    reselect_studenten();
  });

  function shuffleArray(array) {
      for (var i = array.length - 1; i > 0; i--) {
          var j = Math.floor(Math.random() * (i + 1));
          var temp = array[i];
          array[i] = array[j];
          array[j] = temp;
      }
      return array;
  }

  function reselect_studenten() {
    selected_studenten = [];
    $(".groep:checked").each(function(i, checkbox) {
      $(groepen[checkbox.id]).each(function(i, student) {
        selected_studenten.push(student);
      });
    });
    shuffleArray(selected_studenten);

    nieuwenaam();
  }


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
      focus: function(event, ui) {
        console.log(event, ui);
        // Find student
        for(id in studenten) {
          if(studenten[id].naam == ui.item.value) {
            $('#helper').show().attr('src', studenten[id].foto);
          }
        }
      },
      change: function(event, ui) {
        $('#helper').attr('src','').hide();
      },
      close: function(event, ui) {
        $('#helper').attr('src', '').hide();
      },
      select: function( event, ui ) {
        $('#verberger').fadeOut({complete: function() {
          var feedback;
          if(ui.item.value == student.naam) {
            feedback = $('#correct')
          } else {
            feedback = $('#helaas');
          }
          feedback.fadeIn().fadeOut();
        }}).delay(400).fadeIn({complete: function() {
          nieuwenaam();
          $('#naam').val('');
        }});

        return true;
      }
    });

  function nieuwenaam() {
    if(selected_studenten.length == 0) {
      $('#foto img').attr('src', '');
      student = undefined;
      return;
    }

    raad_index++;
    if(raad_index % selected_studenten.length == 0) {
      shuffleArray(selected_studenten);
    }
    student = selected_studenten[raad_index % selected_studenten.length];

    $('#foto img').attr('src', student.foto)

    var points = '';
    for(i in student.vierkant) {
      points += student.vierkant[i][0] + ',' + student.vierkant[i][1] + ' ';
    }

    $('svg')[0].setAttribute('viewBox', '0 0 ' + student.grootte[0] + ' ' + student.grootte[1]);
    $('#vierkant').attr('points', points);
  }

  reselect_studenten();

  $('#naam').keypress(function(e) {
    if(e.which == 13) {
      $('#verberger').fadeOut().delay(300).fadeIn({complete: function() {
        nieuwenaam();
      }});
    }
  })

});
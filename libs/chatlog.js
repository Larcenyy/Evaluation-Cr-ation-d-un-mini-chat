function updateMessages() {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/api.php');
    xhr.responseType = 'json';

    xhr.onload = function() {
        if (xhr.status === 200) {
            // Mettre à jour le contenu de la page avec les données reçues
            // document.querySelector('.chat-left').innerHTML += xhr.response.message;
            // element => console.log(element)



            xhr.response.forEach( (event) => {
                const showBox = document.createElement("div");
                showBox.classList.add("showBox");

                const container = document.createElement("div");

                // const pseudoSpan = document.createElement("span");
                // pseudoSpan.innerHTML = "<i class='fas fa-circle' style='color: " + color + ";'></i>" + event.pseudo;
                // container.appendChild(pseudoSpan);
                //
                // const dateSpan = document.createElement("span");
                // dateSpan.innerHTML = "<i class='fas fa-clock'></i>" + event.date;
                // container.appendChild(dateSpan);

                showBox.appendChild(container);

                const messageParagraph = document.createElement("p");
                messageParagraph.textContent = event.message;
                showBox.appendChild(messageParagraph);

                document.querySelector('.chat-left').appendChild(showBox)
            })
        }
        else {
            console.error('Erreur lors de la mise à jour des messages');
        }
    };

    xhr.send();
}

// Mettre à jour les messages toutes les 3 secondes
setInterval(updateMessages, 3000);

const API_URL = 'http://localhost:3000/movies'; // change port if you ran json-server on another port

const movieListDiv = document.getElementById('movie-list');
const searchInput = document.getElementById('search-input');
const form = document.getElementById('add-movie-form');

let allMovies = []; // full list cached locally

// Render movies
function renderMovies(moviesToDisplay) {
  movieListDiv.innerHTML = '';
  if (!moviesToDisplay || moviesToDisplay.length === 0) {
    movieListDiv.innerHTML = '<p>No movies found matching your criteria.</p>';
    return;
  }

  moviesToDisplay.forEach(movie => {
    const movieElement = document.createElement('div');
    movieElement.classList.add('movie-item');
    movieElement.dataset.id = movie.id; // store id for delegation

    movieElement.innerHTML = `
      <div>
        <p><strong>${escapeHtml(movie.title)}</strong> (${movie.year}) - ${escapeHtml(movie.genre)}</p>
      </div>
      <div class="movie-actions">
        <button class="edit-btn" data-id="${movie.id}">Edit</button>
        <button class="delete-btn" data-id="${movie.id}">Delete</button>
      </div>
    `;
    movieListDiv.appendChild(movieElement);
  });
}

// Simple HTML escape to avoid injection issues
function escapeHtml(text) {
  if (!text) return '';
  return text.replace(/[&<>"']/g, function (m) {
    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
  });
}

// Fetch all movies (READ)
function fetchMovies() {
  fetch(API_URL)
    .then(resp => {
      if (!resp.ok) throw new Error('Network response was not ok');
      return resp.json();
    })
    .then(movies => {
      allMovies = movies;
      renderMovies(allMovies);
    })
    .catch(err => {
      console.error('Error fetching movies:', err);
      movieListDiv.innerHTML = '<p style="color:red">Failed to load movies. Is JSON Server running?</p>';
    });
}

// Search filtering
searchInput.addEventListener('input', () => {
  const term = searchInput.value.trim().toLowerCase();
  if (!term) {
    renderMovies(allMovies);
    return;
  }
  const filtered = allMovies.filter(m => (m.title || '').toLowerCase().includes(term) ||
                                        (m.genre || '').toLowerCase().includes(term));
  renderMovies(filtered);
});

// Create (POST)
form.addEventListener('submit', (e) => {
  e.preventDefault();
  const newMovie = {
    title: document.getElementById('title').value.trim(),
    genre: document.getElementById('genre').value.trim(),
    year: parseInt(document.getElementById('year').value, 10)
  };

  // Basic validation
  if (!newMovie.title || isNaN(newMovie.year)) {
    alert('Please provide a valid title and year.');
    return;
  }

  fetch(API_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(newMovie)
  })
    .then(res => {
      if (!res.ok) throw new Error('Failed to add movie');
      return res.json();
    })
    .then(added => {
      form.reset();
      fetchMovies();
    })
    .catch(err => console.error('Error adding movie:', err));
});

// Event delegation for Edit and Delete buttons
movieListDiv.addEventListener('click', (e) => {
  const target = e.target;
  if (target.classList.contains('delete-btn')) {
    const id = target.dataset.id;
    if (confirm('Delete this movie?')) deleteMovie(id);
  } else if (target.classList.contains('edit-btn')) {
    const id = target.dataset.id;
    const movie = allMovies.find(m => String(m.id) === String(id));
    if (!movie) return alert('Movie not found.');
    editMoviePrompt(movie);
  }
});

// DELETE
function deleteMovie(movieId) {
  fetch(`${API_URL}/${movieId}`, { method: 'DELETE' })
    .then(res => {
      if (!res.ok) throw new Error('Failed to delete movie');
      fetchMovies();
    })
    .catch(err => console.error('Error deleting movie:', err));
}

// UPDATE (collect via prompts)
function editMoviePrompt(movie) {
  const newTitle = prompt('Enter new Title:', movie.title);
  if (newTitle === null) return; // user cancelled
  const newYearStr = prompt('Enter new Year:', movie.year);
  if (newYearStr === null) return;
  const newGenre = prompt('Enter new Genre:', movie.genre);
  if (newGenre === null) return;

  const updatedMovie = {
    id: movie.id,
    title: newTitle.trim(),
    year: parseInt(newYearStr, 10),
    genre: newGenre.trim()
  };

  if (!updatedMovie.title || isNaN(updatedMovie.year)) {
    alert('Please provide a valid title and year.');
    return;
  }

  updateMovie(movie.id, updatedMovie);
}

function updateMovie(movieId, updatedMovieData) {
  fetch(`${API_URL}/${movieId}`, {
    method: 'PUT', // JSON Server supports PUT (replace) or PATCH (partial)
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(updatedMovieData)
  })
    .then(res => {
      if (!res.ok) throw new Error('Failed to update movie');
      return res.json();
    })
    .then(() => fetchMovies())
    .catch(err => console.error('Error updating movie:', err));
}

// Initial load
fetchMovies();

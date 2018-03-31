function updateForm() {

    let volume = $.parseJSON($('#search-results').val());
    let volumeInfo = volume.volumeInfo;

    function exists(thing) {
        return typeof thing !== 'undefined' && thing !== null;
    }

    function setValueIfPresent(element, value) {
        if (exists(value)) {
            element.val(value);
        }
    }

    function setGoogleLinks(googleBooksId) {
        let booksUrl = 'http://books.google.co.uk/books?id=' + googleBooksId;
        let googleBooksReviewsUrl = booksUrl + "&sitesec=reviews";
        setValueIfPresent($('#bookrater_raterbundle_book_googleBooksUrl'), booksUrl);
        setValueIfPresent($('#bookrater_raterbundle_book_googleBooksReviewsUrl'), googleBooksReviewsUrl);
    }

    function setISBNs(industryIdentifiers) {
        function getIsbn(type) {
            let isbnIdentifier = industryIdentifiers.find(x => x.type === type);
            return exists(isbnIdentifier) ? isbnIdentifier.identifier : '';
        }
        if ($.isArray(industryIdentifiers)) {
            setValueIfPresent($('#bookrater_raterbundle_book_isbn'), getIsbn('ISBN_10'));
            setValueIfPresent($('#bookrater_raterbundle_book_isbn13'), getIsbn('ISBN_13'));
        }
    }

    function setDate(date) {
        date = date.split("-");
        date = date.map(it => parseInt(it));
        setValueIfPresent($('#bookrater_raterbundle_book_publishDate_year'), date[0]);
        setValueIfPresent($('#bookrater_raterbundle_book_publishDate_month'), date[1]);
        setValueIfPresent($('#bookrater_raterbundle_book_publishDate_day'), date[2]);
    }

    function setAuthors(authors) {
        function formatAuthorName(option) {
            let savedAuthor = option.text.replace(',', '').toLowerCase().split(' ');
            savedAuthor.push(savedAuthor.shift());
            return savedAuthor;
        }
        const options = $('#bookrater_raterbundle_book_authors')[0].options;
        $.each(options, (i, option) => {
            option.selected = false;
        });

        $.each(authors, function (i, author) {
            author = author.replace(/[^a-zA-Z\- ]/g, '').toLowerCase().split(/\s/);

            let potentials = Array.from(options).filter(option => {
                let savedAuthor = formatAuthorName(option);
                if  (author[author.length - 1] !== savedAuthor[savedAuthor.length - 1]) {
                    return false;
                }
                let firstNameMatches = true;
                let firstName = author[0];
                let savedFirstName = savedAuthor[0];
                for (let i = 0; i < firstName.length - 1; i++) {
                    firstNameMatches = firstName[i] === savedFirstName[i];
                }
                return firstNameMatches;
            });
            if (potentials.length) {
                let selected = potentials[0];
                if (authors.length > 2) {
                    for (let i = 1; i < author.length - 2; i++) {
                        potentials = potentials.filter(option => {
                            let savedAuthor = formatAuthorName(option);
                            return savedAuthor.length > (i + 1) && author[i][0] === savedAuthor[i][0]
                        });
                        if (potentials.length) {
                            selected = potentials[0];
                        }
                    }
                }
                selected.selected = true;
            }
        });
    }

    setValueIfPresent($('#bookrater_raterbundle_book_googleBooksId'), volume.id);
    setValueIfPresent($('#bookrater_raterbundle_book_title'), volumeInfo.title);
    setValueIfPresent($('#bookrater_raterbundle_book_publisher'), volumeInfo.publisher);
    setValueIfPresent($('#bookrater_raterbundle_book_description'), volumeInfo.description);
    setValueIfPresent($('#bookrater_raterbundle_book_googleBooksRating'), volumeInfo.averageRating);

    setGoogleLinks(volume.id);
    setISBNs(volumeInfo.industryIdentifiers);
    setDate(volumeInfo.publishedDate);
    setAuthors(volumeInfo.authors);
}

function getBooks() {
    let key = $('#query-type').val();
    let value = $('#search-term').val();
    const searchResultsDropdown = $('#search-results');
    const searchFailMessage = $('#search-fail');
    const acceptButton = $('#accept');
    function clearSearch() {
        searchResultsDropdown.find('option').remove().end();
    }

    function showNoResults() {
        searchFailMessage.show();
        acceptButton.hide();
        searchResultsDropdown.hide();
    }

    function showResults(json) {
        searchFailMessage.hide();
        acceptButton.show();
        searchResultsDropdown.show();
        $.each(json.items, (i, volume) => {
            let author = "";
            if (typeof volume.volumeInfo.authors !== 'undefined') {
                author = ", (" + volume.volumeInfo.authors.join('; ') + ")";
            }
            searchResultsDropdown.append($('<option>', {
                value: JSON.stringify(volume),
                text: volume.volumeInfo.title + author
            }));
        });
    }

    $.ajax({
        url: 'https://www.googleapis.com/books/v1/volumes?q=' + key + ':' + value,
        success: function (json) {
            clearSearch();
            if (json.items.length === 0) {
                showNoResults();
            } else {
                showResults(json);
            }
        }
    });
}

Array.prototype.swap = function(firstIndex, secondIndex) {
    let input = this;
    let temp = input[firstIndex];
    input[firstIndex] = input[secondIndex];
    input[secondIndex] = temp;
    return input;
};

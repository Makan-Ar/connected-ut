<?php
namespace App\Services;

use App\Models\Book;
use App\Contracts\ISearch;
use App\Models\Tags\course;
use App\Models\Tags\Instructor;
use \Illuminate\Support\Facades\DB;


class SearchService implements ISearch
{
    protected $table;
    protected $selectStatement;
    protected $bindings;

    /**
     * sets the protected variables
     */
    public function __construct()
    {
        $this->selectStatement = "";
        $this->bindings = [];
    }

    /**
     * Sets the table which to be used for search
     *
     * @param $index
     * @return $this
     */
    public function on($index)
    {
        $this->table = $index;

        return $this;
    }

    /**
     * Runs the prepared query and sorts the most repeated results
     *
     * @return array
     */
    public function get()
    {
        if($this->selectStatement != "")
            $result = collect(DB::select(DB::raw($this->selectStatement), $this->bindings))->lists('book_id')->all();
        else
            $result = [];

        $result = $this->sortByMostRepeated($result);
        $result = Book::whereIn('id', $result);

        return $result;
    }

    /**
     * Prepares a select statement based on keywords passed
     *
     * @param $keywords
     * @return $this
     */
    public function by($keywords)
    {
        $regexKeywords = preg_replace('/\b[a-z]{1,2}\b/', '', $keywords);
        $regexKeywords = preg_replace('/\s+/', '|', trim($regexKeywords));
        if($regexKeywords != "")
        {
            $this->bindings['keywords'] = $regexKeywords;
            $this->selectStatement = "SELECT `id` AS 'book_id' FROM `books` WHERE `title` REGEXP :keywords";
        }

//        $courses = collect(
//                        DB::select(DB::raw("SELECT `id` FROM `courses` WHERE `full_course_name` REGEXP :keywords AS Flag"), ['keywords' => $regexKeywords])
//                    )->lists('id')->all();
//
//        $i = implode("','", $courses);
//        $bookIds = collect(
//            DB::select(DB::raw("SELECT `book_id` FROM `book_course` WHERE `course_id` IN ('$i')"))
//        )->lists('book_id')->all();
//        dd($bookIds);
//        dd(Book::whereIn('id', $bookIds)->get());

        return $this;
    }

    /**
     * Prepares a select query based on the filters passed
     *
     * @param array $filters
     * @return $this
     */
    public function filter(array $filters)
    {
        $filteredBookIds = "";

        if(array_key_exists('title', $filters))
        {
            $filters['title'] = preg_replace('/\b[a-z]{1,2}\b/', '', $filters['title']);
            $this->bindings['title'] = preg_replace('/\s+/', '|', trim($filters['title']));
            $filteredBookIds .= "SELECT `id` AS 'book_id' FROM `books` WHERE `title` REGEXP :title";
        }

        if(array_key_exists('course_list', $filters))
        {
            $this->deleteNonNumerics($filters['course_list']);
            $course_ids = implode("','", $filters['course_list']);
            $filteredBookIds .= ($filteredBookIds != "")? " UNION ALL " : "";
            $filteredBookIds .= "SELECT `book_id` FROM `book_course` WHERE `course_id` IN ('$course_ids')";
        }

        if(array_key_exists('author_list', $filters))
        {
            $this->deleteNonNumerics($filters['author_list']);
            $author_ids = implode("','", $filters['author_list']);
            $filteredBookIds .= ($filteredBookIds != "")? " UNION ALL " : "";
            $filteredBookIds .= "SELECT `book_id` FROM `author_book` WHERE `author_id` IN ('$author_ids')";
        }

        if(array_key_exists('instructor_list', $filters))
        {
            $this->deleteNonNumerics($filters['instructor_list']);
            $instructor_ids = implode("','", $filters['instructor_list']);
            $filteredBookIds .= ($filteredBookIds != "")? " UNION ALL " : "";
            $filteredBookIds .= "SELECT `book_id` FROM `book_instructor` WHERE `instructor_id` IN ('$instructor_ids')";
        }

        if(array_key_exists('ISBN_13', $filters) && is_numeric($filters['ISBN_13']))
        {
            $this->bindings['ISBN_13'] = $filters['ISBN_13'];
            $filteredBookIds .= ($filteredBookIds != "")? " UNION ALL " : "";
            $filteredBookIds .= "SELECT `id` AS 'book_id' FROM `books` WHERE `ISBN_13` = :ISBN_13";
        }

        if(array_key_exists('ISBN_10', $filters)  && is_numeric($filters['ISBN_10']))
        {
            $this->bindings['ISBN_10'] = $filters['ISBN_10'];
            $filteredBookIds .= ($filteredBookIds != "")? " UNION ALL " : "";
            $filteredBookIds .= "SELECT `id` AS 'book_id' FROM `books` WHERE `ISBN_10` = :ISBN_10";
        }

        $this->selectStatement = $filteredBookIds;
        return $this;
    }

    /**
     * Sorts the result based on the most repeated records
     *
     * @param array $result
     * @return array
     */
    private function sortByMostRepeated(array $result)
    {
        $count = array_count_values($result);
        arsort($count);

        return array_keys($count);
    }

    /**
     * Unsets array elements if not numeric
     *
     * @param array $numbers
     */
    private function deleteNonNumerics(array &$numbers)
    {
        foreach($numbers as $key => $val)
        {
            if(! is_numeric($val))
                unset($numbers[$key]);
        }
    }
}
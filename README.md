# php-seating-chart
Seating chart for movie theatre written in PHP 5.6

## Run from Command Line

```$ php reservationDriver.php < reservations.txt ```

Or if you want to pipe output into a separate file

```$ php reservationDriver.php < reservations.txt > output.txt```

If you want to see the seating Chart and a tabular representation of the Manhattan Distance from any given seat to the best seat, change the second parameter when instantiating $reservationDriver to true.

There are also basic performance metrics at the bottom of the reservationDriver.php file that I used when developing this solution. They can be uncommented to see performance.

## Run in Docker Container

```sh
# build app image
$ docker build -t php-seating-chart .
# run app demo with image
$ docker run --rm --name seating-chart-demo php-seating-chart
```

## Interview Puzzle
Your younger sister is putting on a puppet show in your family's back yard. She has left you in charge of ticketing the big event. She has informed you that she wants assigned seating. She plans on setting up 33 seats; 3 rows with 11 seats each. She already has several seats reserved for her parents and best friends. Being a good computer scientist, you decide to whip up a quick program to help her out.

## Instructions
Write a program in two pieces, as described below:

## Seating Chart
Create a data structure which represents a seating chart. This chart should have, at a minimum, the following functionality:

* The seating chart should be able to be initialized to a specific number of rows and seats.
* The seating chart should be able to mark a specific seat as reserved.
* The seating chart should be able to return if a specific seat is reserved.
* Given a request for a number of seats, the seating chart should be able to find the best available group of consecutive seats.

## Driver Program
Create a driver program which takes input on stdin and outputs to stdout. The driver program should:

Create a seating chart with 3 rows and 11 columns.
* The first line of input (Up to a newline character) should take input in the form of: R1C1 R1C4 R2C5. These seats are the initial reservations. In this case, the seats at:
** Row 1, Column 1
** Row 1, Column 4
** Row 2, Column 5
Should all be marked as reserved before taking reservation requests. This line can be blank.
* Subsequent lines of input (Up to an EOF character) should take integers representing the number of consecutive seats to reserve. For example: 5 represents a request for 5 consecutive seats.
** If a group of consecutive seats matching the requested criteria is found, reserve those seats and print out the range of the reservation in the following format: R3C1 - R3C6. This example would represent a reservation of 6 seats 1, 2, 3, 4, 5 and 6 in row 3. A lone seat should be output as simply R3C1 (Seat 1 in row 3)
** If there are not enough consecutive seats to fulfill a request, output Not Available.
* Upon EOF, output the number of remaining available seats

## Additional Notes
* Always try to reserve the best available block of seats. How "good" a particular seat is is simply the Manhattan Distance from the front center seat (in this case R1C6).
* The solution should be flexible enough so that it can work well with a variable number of seats and rows.
* The maximum number of tickets someone can request for the example is 10.
* You are to choose both the data structure of your choice for representing the map, and the algorithm of your choice to return the best available group as efficiently as possible.
* Seat reservations cannot span more than one row, e.g. for a 2 seat request, R1C3 - R1C4 is valid but R1C3 - R2C3 is not.
* See the Sample Input and Sample Output section for input/output from the driver program.

## Solutions Judged On
* Correctness
* Performance (but don't get too hung up here, after all "premature optimization is the root of all evil")
* Clean, commented and maintainable code
* Bonus for clever solutions

## Submission

We are looking for clever, well-documented, maintinable and efficient solutions, although don't get too caught up with nitty gritty optimization. You may write the solution in whatever language you are most comfortable with. If your solution requires external dependencies(npm packages, go packages, CocoaPods, etc), please include instructions to build and/or run. If you have any recent source code you would like to share (or a GitHub account) please feel free to do so as well.

## Sample Input
Your seating chart should have initial 6 reservations for friends and family. 3 groups of 3 ask to attend, followed by a lone attendee. Finally, a single group of 10 ask to sit together. The input for this example would be:

```
R1C4 R1C6 R2C3 R2C7 R3C9 R3C10
3
3
3
1
10
```

## Sample Output

You easily seat the 3 groups of 3 and the solo attendee, but unfortunately there are not 10 consecutive seats for the large group. Your program should output:

```
R1C7 - R1C9
R2C4 - R2C6
R3C5 - R3C7
R1C5
Not Available
17
```

This is the state of the seating chart at termination to aid your visualization.

```
1	2	3	4	5	6	7	8	9	10	11
1	 	 	 	x	o	x	o	o	o	 	 
2	 	 	x	o	o	o	x	 	 	 	 
3	 	 	 	 	o	o	o	 	x	x	 
x - Initial reservation; o - Reserved by best available selection
Note this is only for reference, tabular format of output is not required.
```

<?php include('db_connect.php'); ?>
<table class="table table-bordered table-hover">
  <thead class="table-dark">
    <tr>
      <th>ID</th><th>Name</th>
      <th>Branch</th><th>CGPA</th>
    </tr>
  </thead>
  <tbody>
<?php
$sql = "SELECT * FROM students";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['branch'] . "</td>";
    echo "<td>" . $row['cgpa'] . "</td>";
    echo "</tr>";
}
?>
  </tbody>
</table>
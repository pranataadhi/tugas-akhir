<?php

use PHPUnit\Framework\TestCase;
use App\TaskManager;

class TaskManagerTest extends TestCase
{
    private $pdo;
    private $stmt;
    private $manager;

    protected function setUp(): void
    {
        // Kita buat Database Palsu (Mock)
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);

        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->manager = new TaskManager($this->pdo);
    }

    public function testAddTask()
    {
        $this->stmt->expects($this->once())->method('execute')->willReturn(true);
        $result = $this->manager->addTask("Belajar Jenkins");
        $this->assertTrue($result);
    }

    public function testDeleteTask()
    {
        $this->stmt->expects($this->once())->method('execute')->willReturn(true);
        $result = $this->manager->deleteTask(1);
        $this->assertTrue($result);
    }

    public function testGetAllTasks()
    {
        $this->stmt->method('fetchAll')->willReturn([['id' => 1, 'task_name' => 'Test']]);
        $result = $this->manager->getAllTasks();
        $this->assertCount(1, $result);
    }

    public function testSearchTasks()
    {
        $this->stmt->method('fetchAll')->willReturn([['id' => 1, 'task_name' => 'Cari']]);
        $result = $this->manager->searchTasks('Cari');
        $this->assertCount(1, $result);
    }
}

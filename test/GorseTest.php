<?php

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

final class GorseTest extends TestCase
{
    const ENDPOINT = "http://127.0.0.1:8088/";
    const API_KEY = "zhenghaoz";

   /**
 * @throws GuzzleException
 */
public function testInsertAndDeleteUser(): void
{
    $client = new Gorse(self::ENDPOINT, self::API_KEY);
    $userDetails = [
        'UserId' => 'user123',
        'Comment' => 'Test User',
        'Labels' => ['label1', 'label2'],
        'Subscribe' => ['topic1', 'topic2']
    ];
    // Insert user with details.
    $rowsAffected = $client->insertUserWithDetails('user123', 'Test User', ['label1', 'label2'], ['topic1', 'topic2']);
    $this->assertEquals(1, $rowsAffected->rowAffected);
    // Get user details.
    $user = $client->getUserDetails('user123');
    $this->assertEquals($userDetails, (array)$user);
    // Delete user.
    $rowsAffected = $client->deleteUser('user123');
    $this->assertEquals(1, $rowsAffected->rowAffected);
}

/**
 * @throws GuzzleException
 */
public function testItemOperations(): void
{
    $client = new Gorse(self::ENDPOINT, self::API_KEY);
    $item = [
        'ItemId' => 'item123',
        'Categories' => ['cat1', 'cat2'],
        'Comment' => 'Test Item',
        'IsHidden' => false,
        'Labels' => ['label1', 'label2'],
        'Timestamp' => '2022-11-20T13:55:27Z'
    ];
    // Insert an item.
    $rowsAffected = $client->insertItem($item);
    $this->assertEquals(1, $rowsAffected->rowAffected);
    // Get the item.
    $returnedItem = $client->getItem('item123');
    $this->assertEquals($item, (array)$returnedItem);
    // Delete the item.
    $rowsAffected = $client->deleteItem('item123');
    $this->assertEquals(1, $rowsAffected->rowAffected);
}

/**
 * @throws GuzzleException
 */
public function testFeedbackOperations(): void
{
    $client = new Gorse(self::ENDPOINT, self::API_KEY);
    $feedback = [
        new Feedback("like", "user1", "item1", "2023-01-01T12:00:00Z"),
        new Feedback("view", "user2", "item2", "2023-01-02T12:00:00Z"),
    ];
    // Insert feedback.
    $rowsAffected = $client->insertFeedback($feedback);
    $this->assertEquals(2, $rowsAffected->rowAffected);
    // Get feedback.
    $feedbacks = $client->getFeedback('', 10);
    $this->assertNotEmpty($feedbacks);
    // Delete feedback with specific type, user, and item.
    $rowsAffected = $client->delFeedback("like", "user1", "item1");
    $this->assertEquals(1, $rowsAffected->rowAffected);
}

/**
 * @throws GuzzleException
 */
public function testMultipleUsersOperations(): void
{
    $client = new Gorse(self::ENDPOINT, self::API_KEY);
    $users = [
        new User("2", ["d", "e", "f"]),
        new User("3", ["g", "h", "i"]),
    ];
    // Insert multiple users.
    $rowsAffected = $client->insertUsers($users);
    $this->assertEquals(2, $rowsAffected->rowAffected);
    // Get users with pagination.
    $usersResult = $client->getUsers('', 2);
    $this->assertNotEmpty($usersResult);
    // Deleting multiple users.
    foreach ($users as $user) {
        $rowsAffected = $client->deleteUser($user->userId);
        $this->assertEquals(1, $rowsAffected->rowAffected);
    }
}

/**
 * @throws GuzzleException
 */
public function testMultipleItemsOperations(): void
{
    $client = new Gorse(self::ENDPOINT, self::API_KEY);
    $items = [
        ['ItemId' => 'item1', 'Categories' => ['cat1'], 'Comment' => 'First item', 'IsHidden' => false, 'Labels' => ['label1'], 'Timestamp' => '2023-01-01T00:00:00Z'],
        ['ItemId' => 'item2', 'Categories' => ['cat2'], 'Comment' => 'Second item', 'IsHidden' => false, 'Labels' => ['label2'], 'Timestamp' => '2023-01-02T00:00:00Z'],
    ];
    // Insert multiple items.
    $rowsAffected = $client->insertItems($items);
    $this->assertEquals(2, $rowsAffected->rowAffected);
    // Get items with pagination.
    $itemsResult = $client->getItems('', 2);
    $this->assertNotEmpty($itemsResult);
    // Deleting multiple items.
    foreach ($items as $item) {
        $rowsAffected = $client->deleteItem($item['ItemId']);
        $this->assertEquals(1, $rowsAffected->rowAffected);
    }
}

    /**
     * @throws RedisException|GuzzleException
     */
    public function testRecommend()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $redis->zAdd('offline_recommend/10', [], 1, '10', 2, '20', 3, '30');
        $client = new Gorse(self::ENDPOINT, self::API_KEY);
        $items = $client->getRecommend('10');
        $this->assertEquals(['30', '20', '10'], $items);
    }
}

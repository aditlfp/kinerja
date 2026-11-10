<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CheckpointQueryIndexesTest extends TestCase
{
    public function test_checkpoint_query_indexes_are_present_when_schema_is_migrated(): void
    {
        $this->assertTrue(Schema::hasIndex('check_point_items', 'cp_items_checkpoint_tanggal_idx'));
        $this->assertTrue(Schema::hasIndex('check_point_messages', 'cp_messages_item_pending_idx'));
    }
}

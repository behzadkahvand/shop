<?php

namespace App\Tests\Unit\Service\ORM\Extension\Utils;

use App\Service\ORM\Extension\Utils\QueryBuilderMethodInvoker;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class QueryBuilderMethodInvokerTest
 */
final class QueryBuilderMethodInvokerTest extends MockeryTestCase
{
    public function testItInvokeBetweenMethod()
    {
        $method      = 'between';
        $alias       = 'foo.bar';
        $operator    = 'BETWEEN';
        $value       = [1, 2];
        $betweenExpr = \Mockery::mock(Func::class);
        $expr        = \Mockery::mock(Expr::class);
        $expr->shouldReceive('between')
             ->once()
             ->with($alias, \Mockery::type('string'), \Mockery::type('string'))
             ->andReturn($betweenExpr);

        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('expr')
           ->once()
           ->withNoArgs()
           ->andReturn($expr);

        $qb->shouldReceive('andWhere')->once()->with($betweenExpr)->andReturnSelf();
        $qb->shouldReceive('setParameter')->once()->with(\Mockery::type('string'), $value[0])->andReturnSelf();
        $qb->shouldReceive('setParameter')->once()->with(\Mockery::type('string'), $value[1])->andReturnSelf();

        $invoker = new QueryBuilderMethodInvoker();
        $invoker->invoke($qb, $method, $alias, $operator, $value);
    }

    public function testItInvokeIsNullAndIsNotNullMethod()
    {
        $method     = 'isNull';
        $alias      = 'foo.bar';
        $operator   = 'IS NULL';
        $value      = [1, 2];
        $isNullExpr = \Mockery::mock(Func::class);
        $expr       = \Mockery::mock(Expr::class);
        $expr->shouldReceive('isNull')
             ->once()
             ->with($alias)
             ->andReturn($isNullExpr);

        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('expr')
           ->once()
           ->withNoArgs()
           ->andReturn($expr);

        $qb->shouldReceive('andWhere')->once()->with($isNullExpr)->andReturnSelf();

        $invoker = new QueryBuilderMethodInvoker();
        $invoker->invoke($qb, $method, $alias, $operator, $value);

        $method     = 'isNotNull';
        $alias      = 'foo.bar';
        $operator   = 'IS NOT NULL';
        $value      = [1, 2];
        $isNullExpr = \Mockery::mock(Func::class);
        $expr       = \Mockery::mock(Expr::class);
        $expr->shouldReceive('isNotNull')
             ->once()
             ->with($alias)
             ->andReturn($isNullExpr);

        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('expr')
           ->once()
           ->withNoArgs()
           ->andReturn($expr);

        $qb->shouldReceive('andWhere')->once()->with($isNullExpr)->andReturnSelf();

        $invoker->invoke($qb, $method, $alias, $operator, $value);
    }

    public function testItApplyLikeMethod()
    {
        $method   = 'like';
        $alias    = 'foo.bar';
        $operator = 'like';
        $value    = 'foobar%';
        $likeExpr = \Mockery::mock(Func::class);
        $expr     = \Mockery::mock(Expr::class);
        $expr->shouldReceive('like')
             ->once()
             ->with($alias, \Mockery::type('string'))
             ->andReturn($likeExpr);

        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('expr')
           ->once()
           ->withNoArgs()
           ->andReturn($expr);

        $qb->shouldReceive('andWhere')->once()->with($likeExpr)->andReturnSelf();
        $qb->shouldReceive('setParameter')->once()->with(\Mockery::type('string'), '%foobar%')->andReturnSelf();

        $invoker = new QueryBuilderMethodInvoker();
        $invoker->invoke($qb, $method, $alias, $operator, $value);
    }

    public function testItApplyExprMethods()
    {
        $method   = 'eq';
        $alias    = 'foo.bar';
        $operator = 'eq';
        $value    = 'foobar';
        $eqExpr   = \Mockery::mock(Func::class);
        $expr     = \Mockery::mock(Expr::class);
        $expr->shouldReceive('eq')
             ->once()
             ->with($alias, \Mockery::type('string'))
             ->andReturn($eqExpr);

        $qb = \Mockery::mock(QueryBuilder::class);
        $qb->shouldReceive('expr')
           ->once()
           ->withNoArgs()
           ->andReturn($expr);

        $qb->shouldReceive('andWhere')->once()->with($eqExpr)->andReturnSelf();
        $qb->shouldReceive('setParameter')->once()->with(\Mockery::type('string'), 'foobar')->andReturnSelf();

        $invoker = new QueryBuilderMethodInvoker();
        $invoker->invoke($qb, $method, $alias, $operator, $value);
    }
}
